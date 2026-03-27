#!/bin/bash
# Usage: ./execArtisan.sh [artisan arguments]
# Example: ./execArtisan.sh migrate --seed

# Name of the Docker container running your Laravel app
CONTAINER_NAME="chatprojects-app-1"

# If you want to auto-detect the container, you can use:
# CONTAINER_NAME=$(docker compose ps --services | grep app | head -n1)

# Run artisan command inside the container
if [ $# -eq 0 ]; then
    echo "Usage: $0 [artisan arguments]"
    echo "Example: $0 migrate --seed"
    exit 1
fi

docker exec -it "$CONTAINER_NAME" php artisan "$@"
