<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProjectionSyncEvent;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::query()
            ->activeProjection()
            ->withCount(['epics', 'repositories']);

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('code_folder', 'like', "%{$search}%")
                    ->orWhere('local_location', 'like', "%{$search}%")
                    ->orWhere('github_repo', 'like', "%{$search}%")
                    ->orWhere('gitea_location', 'like', "%{$search}%")
                    ->orWhere('framework_description', 'like', "%{$search}%")
                    ->orWhere('languages', 'like', "%{$search}%");
            });
        }

        $projects = $query
            ->orderBy('name')
            ->paginate(12);

        // Dashboard summary: highlight recent projection sync failures.
        $failedSyncEventsLastHour = ProjectProjectionSyncEvent::query()
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subHour())
            ->count();

        $syncLagWarningMinutes = 20;

        return view('projects.index', compact(
            'projects',
            'failedSyncEventsLastHour',
            'syncLagWarningMinutes'
        ));
    }

    public function show(Project $project)
    {
        $project->load([
            'repositories',
            'epics' => fn ($query) => $query->with('status')->latest()->limit(10),
        ]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        // Load repositories so the edit form can expose per-repo Git links.
        $project->load('repositories');

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'repositories' => 'nullable|array',
            'repositories.*.git_repo_url' => 'nullable|string|max:500',
        ]);

        // Persist per-repository Git links keyed by repository id from the form payload.
        $repositoriesInput = $validated['repositories'] ?? [];

        foreach ($repositoriesInput as $repositoryId => $repositoryPayload) {
            // Guard clause: ignore malformed keys to avoid accidental broad updates.
            if (! is_numeric($repositoryId)) {
                continue;
            }

            $gitRepoUrl = trim((string) ($repositoryPayload['git_repo_url'] ?? ''));

            // Update only repositories that belong to the current project.
            $project->repositories()
                ->whereKey((int) $repositoryId)
                ->update([
                    'git_repo_url' => $gitRepoUrl !== '' ? $gitRepoUrl : null,
                ]);
        }

        return redirect()
            ->route('projects.show', $project)
            ->with('success', 'Repository Git links updated successfully.');
    }
}
