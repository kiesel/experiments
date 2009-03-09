#!/bin/sh
#
# $Id$
#

repositoryBase () {
  local $BASE
  BASE=$(realpath .)
  
  while [ -d "$BASE/../.svn" ]; do
    if [ -d "$BASE/../.svn" ] ; then
      BASE=$(realpath "$BASE/..")
    fi
  done
  
  echo $BASE
}

tmpTagDir () {
  echo "$HOME/.xpsvn/tag"
}

repositoryUrl () {
  local REPO=$1
  svn info $1 | grep '^URL:' | cut -d ' ' -f 2 | ${SED} -r "s#/(trunk|tags|branches)(/)?.*##"
}

repositoryRoot () {
  local REPO=$1
  svn info $1 | grep '^Repository Root:' | cut -d ' ' -f 3
}

fetchTarget() {
  local TARGET=$1
  
  if [ -z $TARGET ]; then
    TARGET="."
  fi
  
  local REAL=$(realpath $TARGET)
  
  if [ ! -e "$REAL" ]; then
    echo "Invalid target specified: $TARGET" >&2;
    return 1;
  fi
  
  echo $REAL;
}

assertHaveActiveTag() {
  if [ ! -d "$(tmpTagDir)"/current-tag ]; then
    echo "You do not have an active tag. Aborting."
    exit 1;
  fi
}

fetchFileRevision() {
  local FILE=$1
  
  svn info --xml "$FILE" | grep revision | tail -n1 | cut -d '"' -f2
}

relativeTarget () {
  local TARGET=$1
  
  echo $TARGET | ${SED} -r "s#$REPOBASE/##"
}


# Find out suitable sed executable
# Tip for FreeBSD users: install /usr/ports/textproc/gsed
SED=$(which gsed sed 2>/dev/null | head -n 1)

# Initially parse command line to find global
# options
while getopts 'vdr:' COMMAND_LINE_ARGUMENT ; do
  case "$COMMAND_LINE_ARGUMENT" in
    v)  VERBOSE="yes";;
    d)  DEBUG="yes";;
    r)  REPOBASE=$(realpath $OPTARG);;
    ?)  exit
  esac
done
shift $(($OPTIND - 1))

[ -z "$REPOBASE" ] && REPOBASE=$(repositoryBase)
[ -z "$REPOBASE" -o ! -e "$REPOBASE" ] && {
  echo "!!! Repository base not found or not specified: '$REPOBASE'";
  exit 1;
}

[ -z "$REPOURL" ] && REPOURL=$(repositoryUrl)
[ -z "$REPOURL" ] && {
  echo "!!! Could not determine repository URL.";
  exit 1;
}

[ "$VERBOSE" = "yes" ] && { 
  echo "===> Global repository information:"
  echo "---> Repository base: $REPOBASE"
  echo "---> Repository url: $REPOURL"
  echo
}

# Reset options indicator for further scans
OPTIND=1
