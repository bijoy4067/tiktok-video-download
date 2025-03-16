#!/bin/bash

# Enable strict mode
set -euo pipefail
IFS=$'\n\t'

# Color definitions
YELLOW=$(tput setaf 3)
GREEN=$(tput setaf 2)
RED=$(tput setaf 1)
BLUE=$(tput setaf 4)
GGG=$(tput setaf 5)
CYN=$(tput setaf 7)
STAND=$(tput sgr 0)
BOLD=$(tput bold)

# URL validation function
validate_tiktok_url() {
    local url="$1"
    if [[ ! "$url" =~ ^https?://(www\.)?tiktok\.com/@[A-Za-z0-9._-]+/video/[0-9]+ ]]; then
        echo -e "${RED}${BOLD}Error: Invalid TikTok URL format${STAND}"
        exit 1
    fi
}

# Sanitize function for filenames
sanitize_filename() {
    echo "$1" | sed 's/[^A-Za-z0-9._-]/_/g'
}

# Input validation
if [ -z "${1:-}" ]; then
    echo -e "${YELLOW}${BOLD}Missing video link URL:${STAND}${BOLD} bash $0 https://www.tiktok.com/@username/video/1234567890${STAND}"
    exit 1
fi

# Validate and sanitize input
validate_tiktok_url "$1"

# Extract and sanitize components
test=$(echo "$1" | cut -d '/' -f5)
user=$(echo "$1" | cut -d '/' -f4 | sed 's/^@//' | tr -cd 'A-Za-z0-9._-')
id=$(echo "$1" | cut -d '/' -f6 | cut -d '?' -f1 | tr -cd '0-9')

if [ "${test}" == "video" ]; then
    aria2c -m 0 -x 10 -c "https://tikwm.com/video/media/hdplay/${id}.mp4" -o "$(sanitize_filename "${user}-${id}.mp4")"
else
    echo -e "${RED}${BOLD}Error: Invalid URL format or parameters${STAND}"
    exit 1
fi