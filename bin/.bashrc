#!/usr/bin/env bash

label() {
    case $2 in
    ok) echo -e "\e[42m$1\e[0m";;
    ko) echo -e "\e[41m$1\e[0m";;
    *) echo -e "\e[34m$1\e[0m";;
    esac
}
export -f label

confirm() {
    if [[ $2 == yes ]]; then label "$1 [Yn]";
    else label "$1 [yN]"; fi
    read answer
    if [[ $2 == yes ]]; then answer=${answer:-y};
    else answer=${answer:-n}; fi
    [[ ${answer} =~ ^y|Y|yes|YES$ ]] && return 1;
    return 0;
}
export -f confirm

load_env() {
    source .env.dist
    [[ -f .env ]] && source .env
}
export -f load_env

run() {
    if [[ ${CI} == true ]]; then
        run_local ${@}
        return $?
    fi
    lando version >/dev/null 2>&1
    if [[ $? -eq 0 ]]; then
        lando bash "${*}"
        return $?
    fi
    run_local ${@}
    return $?
}
export -f run

run_local() {
    bash -xc "${*}" 2>&1
    return $?
}
export -f run_local

run_in_package() {
    local ret=0
    for package in $(find src/*/composer.json -type f); do
        pushd "$(dirname "${package}")" &> /dev/null
        if [[ ${TRAVIS} == true ]]; then
            if [[ $1 == --local ]]; then tfold "[CWD] $(pwd)" ${@:2};
            else tfold "[CWD] $(pwd)" ${@}; fi
        elif [[ $1 == --local ]]; then
            label "[CWD] $(pwd)"
            run_local ${@:2}
        else
            label "[CWD] $(pwd)"
            run ${@}
        fi
        local last=$?
        [[ ${last} -ne 0 ]] && ret=${last}
        popd &> /dev/null
    done
    return ${ret}
}
export -f run_in_package

download_bin() {
    mkdir -p $(dirname "$1") && \
    curl -Lso "$1" "$2" && \
    chmod +x "$1"
}
export -f download_bin

assert_clean() {
    [[ $(git status --porcelain) ]] && label "Working directory is not clean" ko && exit 1
}
export -f assert_clean