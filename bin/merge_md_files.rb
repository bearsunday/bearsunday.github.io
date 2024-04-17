require 'fileutils'

def generate_combined_file(language, intro_message)
  # マークダウンファイルが存在するフォルダ
  source_folder = File.expand_path("../manuals/1.0/#{language}/", __dir__)
  # 結合されたファイルの出力先
  output_file = "manuals/1.0/#{language}/1page.md"

  puts "Does the source folder exist? #{Dir.exist?(source_folder)}"
  raise "Source folder does not exist!" unless File.directory?(source_folder)

  # ファイルを開く
  File.open(output_file, "w") do |combined_file|

    # 全体のヘッダーを書き込む
    combined_file.write("---\nlayout: docs-#{language}\ntitle: 1 Page Manual\ncategory: Manual\npermalink: /manuals/1.0/#{language}/1page.html\n---\n")

    # 追加のメッセージを書き込む
    combined_file.write(intro_message + "\n\n")

    # 指定フォルダ内のすべての.mdファイルを取得し、ソートする
    files = Dir.glob(File.join(source_folder, "*.md")).sort

    # 各ファイルを処理する - ヘッダーを削除
    files.each do |filepath|
      File.open(filepath, "r") do |file|
        # ファイル内容を読む
        content = file.read

        # ヘッダー部分を削除 （"---"で囲まれた部分を削除）
        content.gsub!(/---.*?---/m, '')

        # 出力ファイルに書き込み
        combined_file.write(content + "\n")
      end
    end

  end

  puts "Markdown files have been combined into #{output_file}"
end

# 以下の行を使用して関数を2言語で呼び出す
generate_combined_file("ja", "これはBEAR.Sundayの全てのマニュアルページを一つにまとめたページです。")
generate_combined_file("en", "This page collects all BEAR.Sunday manuals in one place.")
