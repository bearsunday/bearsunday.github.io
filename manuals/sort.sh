#!/bin/bash

# 対象のディレクトリ
dir="1.0/en"

# フォルダリストを保存するファイル
output_file="folders.txt"

# ディレクトリ内のサブフォルダをリストアップしてファイルに保存
find "$dir" -mindepth 1 -maxdepth 1 -type d | sort > "$output_file"
