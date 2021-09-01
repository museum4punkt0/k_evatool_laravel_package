#!/bin/sh

cd "$(dirname "$0")"
cp ./pre-push ../.git/hooks/pre-push

echo "successfully installed pre push hook"
