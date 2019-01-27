#!/usr/bin/env bash

run_composer() {
    docker run --init -it --rm -u $(id -u):$(id -g) -v $(pwd):/app -w /app \
        -e COMPOSER_HOME=/app/var/composer \
	    jakzal/phpqa:php7.2-alpine composer ${@}
}

label() {
    case $2 in
    ok) echo -e "\e[42m$1\e[0m";;
    ko) echo -e "\e[41m$1\e[0m";;
    *) echo -e "\e[34m$1\e[0m";;
    esac
}

confirm() {
    if [[ $2 == yes ]]; then label "$1 [Yn]";
    else label "$1 [yN]"; fi
    read answer
    if [[ $2 == yes ]]; then answer=${answer:-y};
    else answer=${answer:-n}; fi
    [[ ${answer} =~ ^y|Y|yes|YES$ ]] && return 1;
    return 0;
}

git_clean() {
    [[ $(git status --porcelain) ]] && label "Working directory is not clean" ko && exit 1
}

git_sync() {
    local dir="${1:?missing dir}"
    local url="${2:?missing url}"
    local branch="${3:-master}"
    if [[ ! -d "${dir}/.git" ]]; then
        mkdir -p "${dir}" && \
        git clone --branch "${branch}" "${url}" "${dir}"
    else
        git -C "${dir}" tag -l | xargs -n 1 -I {} git -C "${dir}" tag --delete {} >/dev/null && \
        git -C "${dir}" fetch --all --prune --tags --quiet && \
        git -C "${dir}" checkout --quiet -B "${branch}" "origin/${branch}" && \
        git -C "${dir}" pull origin "${branch}"
    fi
}

package_name() {
    echo "$(echo $(grep -E "^\s*\"name\"\s*:\s*\"msgphp\/([^\"]+)\"\s*,\s*$" "${1:?missing file}") | sed -e "s/^\s*\"name\":\s*\"msgphp\///" -e "s/\"\s*,\s*$//")"
}
