<div class="flex h-screen bg-gray-900 text-gray-100 overflow-hidden font-sans">
    <!-- Left Panel: Rooms -->
    <div class="w-64 bg-gray-800 bg-opacity-50 backdrop-blur-lg border-r border-gray-700 flex flex-col">
        <div class="p-6 border-b border-gray-700">
            <h1 class="font-bold text-lg tracking-wider text-emerald-400 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                Writers Room Live
            </h1>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            @foreach($rooms as $room)
                <button wire:click="selectRoom({{ $room->id }})" 
                        class="w-full text-left px-3 py-2 rounded-lg transition-all {{ $activeRoomId == $room->id ? 'bg-gray-700/80 text-emerald-300 font-semibold border border-gray-600 shadow-sm' : 'hover:bg-gray-800 text-gray-400 hover:text-gray-200' }}">
                    # {{ strtolower(str_replace(' ', '-', $room->name)) }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Center Panel: Chat Stream -->
    <div class="flex-1 flex flex-col bg-[#0B0F19] relative">
        <!-- Header -->
        <div class="h-16 border-b border-gray-800 flex items-center px-8 justify-between bg-gray-900/80 backdrop-blur-md z-10 absolute top-0 w-full shadow-sm">
            <h2 class="font-bold text-xl text-gray-100 flex items-center gap-2">
                # {{ $activeRoomId ? strtolower(str_replace(' ', '-', $rooms->find($activeRoomId)->name)) : 'select-a-room' }}
            </h2>
            <button wire:click="generateOutput" wire:loading.attr="disabled" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-md font-medium transition shadow-lg shadow-indigo-500/20 disabled:opacity-50 flex items-center gap-2 ring-1 ring-indigo-400">
                <span wire:loading.remove wire:target="generateOutput">Generate Writers Room Output</span>
                <span wire:loading wire:target="generateOutput">Initializing...</span>
            </button>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-8 pt-24 pb-32 space-y-8 scroll-smooth" wire:poll.2s>
            @if($messages->isEmpty())
                <div class="flex items-center justify-center h-full text-gray-500 italic">
                    The room is completely silent...
                </div>
            @endif

            @foreach($messages as $msg)
                <div class="flex space-x-4 max-w-4xl mx-auto {{ $msg->role === 'user' ? 'flex-row-reverse space-x-reverse' : '' }}">
                    @if($msg->role !== 'user')
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br {{ $msg->agent_name == 'Producer' ? 'from-amber-500 to-orange-600' : 'from-indigo-500 to-purple-600' }} flex items-center justify-center shadow-lg flex-shrink-0 text-lg font-bold border-2 border-gray-800 text-white">
                        {{ substr($msg->agent_name, 0, 1) }}
                    </div>
                    @endif
                    <div class="flex flex-col {{ $msg->role === 'user' ? 'items-end' : '' }} max-w-[80%]">
                        <span class="text-sm font-semibold text-gray-300 mb-1 ml-1 flex items-center gap-2">
                            {{ $msg->agent_name }} 
                            <span class="text-[10px] text-gray-500 font-normal">{{ $msg->created_at->format('h:i A') }}</span>
                        </span>
                        <div class="px-6 py-4 shadow-sm text-sm leading-relaxed prose prose-invert max-w-none {{ $msg->role === 'user' ? 'bg-indigo-600 text-white rounded-2xl rounded-tr-none' : 'bg-gray-800 text-gray-300 rounded-2xl rounded-tl-none border border-gray-700/50' }}">
                            {!! Str::markdown($msg->content) !!}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Input Overlay -->
        <div class="absolute bottom-0 w-full p-6 bg-gradient-to-t from-[#0B0F19] via-[#0B0F19] to-transparent">
            <div class="max-w-4xl mx-auto relative shadow-2xl">
                <input type="text" placeholder="Message the stream..." class="w-full bg-gray-800/90 backdrop-blur-md border border-gray-700 rounded-lg py-4 px-5 text-gray-200 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition disabled:opacity-50 shadow-inner">
                <button class="absolute right-3 top-3 p-1.5 text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6 transform rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Right Panel: Command Center -->
    <div class="w-80 bg-gray-800 bg-opacity-40 backdrop-blur-xl border-l border-gray-700 p-6 space-y-8 flex flex-col">
        <!-- Active Agents -->
        <div class="bg-gray-900/60 rounded-xl border border-gray-700/50 p-5 shadow-inner">
            <h3 class="text-[11px] uppercase tracking-widest font-bold text-gray-400 mb-4">Active Agents</h3>
            @if($agents->isEmpty())
                <p class="text-sm text-gray-500 italic">No agents active.</p>
            @else
                <div class="space-y-4">
                    @foreach($agents as $agent)
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $agent->name == 'Producer' ? 'from-amber-400 to-orange-500' : 'from-emerald-400 to-teal-600' }} flex items-center justify-center text-sm text-white font-bold shadow-md ring-2 ring-gray-800 ring-offset-1 ring-offset-gray-900">
                                {{ substr($agent->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-200">{{ $agent->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $agent->role ?? 'Logic Node' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Run Status -->
        <div class="bg-gray-900/60 rounded-xl border border-gray-700/50 p-5 shadow-inner" wire:poll.2s>
            <h3 class="text-[11px] uppercase tracking-widest font-bold text-gray-400 mb-4">Run Status</h3>
            <div class="flex items-center space-x-3 bg-gray-800/80 p-3 rounded-lg border border-gray-700">
                @if($activeRun && $activeRun->status === 'running')
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                    </span>
                    <span class="text-sm text-emerald-400 font-semibold tracking-wide">Running...</span>
                @elseif($activeRun && $activeRun->status === 'complete')
                    <span class="h-3 w-3 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.8)]"></span>
                    <span class="text-sm text-indigo-400 font-semibold tracking-wide">Complete</span>
                @else
                    <span class="h-3 w-3 rounded-full bg-gray-500"></span>
                    <span class="text-sm text-gray-400 font-semibold tracking-wide">Idle</span>
                @endif
            </div>
            @if($activeRun)
            <div class="mt-4 pt-4 border-t border-gray-700/50">
                <p class="text-[11px] text-gray-500 mb-1">Active Objective:</p>
                <p class="text-sm text-gray-300 font-medium leading-snug">{{ $activeRun->goal }}</p>
            </div>
            @endif
        </div>

        <!-- Artifacts -->
        <div class="bg-gray-900/60 rounded-xl border border-gray-700/50 p-5 flex-1 shadow-inner">
            <h3 class="text-[11px] uppercase tracking-widest font-bold text-gray-400 mb-4">Generated Artifacts</h3>
            <div class="space-y-3">
                <a href="#" class="flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-indigo-500 group-hover:text-indigo-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="text-sm text-gray-300 group-hover:text-white transition">vision.md</span>
                </a>
                <a href="#" class="flex items-center gap-2 group">
                    <svg class="w-4 h-4 text-emerald-500 group-hover:text-emerald-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span class="text-sm text-gray-300 group-hover:text-white transition">stories.json</span>
                </a>
            </div>
        </div>
    </div>
</div>
