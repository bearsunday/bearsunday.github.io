FROM ruby:3.2

RUN apt-get update && apt-get install -y \
    build-essential \
    libffi-dev

RUN gem install bundler webrick

WORKDIR /app
COPY Gemfile Gemfile.lock ./
RUN bundle install

EXPOSE 4000
CMD ["bundle", "exec", "jekyll", "serve", "--host", "0.0.0.0"]
