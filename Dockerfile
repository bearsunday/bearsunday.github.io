FROM ruby:3.2

RUN apt-get update && apt-get install -y \
    build-essential \
    libffi-dev

RUN gem install bundler webrick

WORKDIR /app
COPY Gemfile Gemfile.lock ./
RUN bundle install

EXPOSE 4001
CMD ["sh", "-lc", "set -e; ruby bin/merge_md_files.rb; bundle exec jekyll serve --host 0.0.0.0 --port 4001 & pid=$!; while [ ! -f _site/index.html ]; do if ! kill -0 $pid 2>/dev/null; then wait $pid; exit 1; fi; sleep 1; done; ./bin/copy_markdown_files.sh; wait $pid"]
