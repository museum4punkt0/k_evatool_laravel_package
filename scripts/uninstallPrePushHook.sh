#!/bin/sh

cd "$(dirname "$0")"
rm ../.git/hooks/pre-push

echo "successfully uninstalled pre push hook"
