#!/bin/bash

# File: .generators/app/install.sh
#
# Install/reinstall critical project dependencies
# like Gulp, wpdtrt-plugin-boilerplate's Yarn dependencies, etc
#
# Note:
# chmod a+x = Change access permissions of install.sh, to 'e[x]ecutable' for '[a]ll users'
#
# Example:
# ---
# chmod a+x install.sh
# sh install.sh
# ---

# 1. Expose the existing OAuth token to Composer
#
# $GH_TOKEN is an export from ~/.bash_profile / Travis Environmental Variables
#
# See
# - <https://github.com/dotherightthing/generator-wpdtrt-plugin-boilerplate/wiki/Set-up-environmental-variables>
# - <https://github.com/dotherightthing/generator-wpdtrt-plugin-boilerplate/issues/88>
#
# Example:
# ---sh
# composer config -g github-oauth.github.com process.env.GH_TOKEN
# ---

# 2. Install the boilerplate dependencies
# and make its gulpfile available for the build.
# composer is installed by travis
# composer reads the generated composer.json
#
# Example:
# ---sh
# composer install --prefer-dist --no-interaction --no-suggest --verbose
# ---

# 3. Install node dependencies
#
# Note:
# - yarn reads the generated package.json & yarn.lock
# - this installs the dev dependency of Gulp
# which is used to run the wpdtrt-plugin-boilerplate gulpfile, below
#
# Example:
# ---sh
# yarn install --non-interactive
# ---

# 4. Run the Gulp build task
#
# Note:
# - gulp-cli is installed by travis
# - gulp is installed with the generator
# - gulp reads ./vendor/dotherightthing/wpdtrt-plugin-boilerplate/gulpfile.js
# - yarn run is equivalent to npm run
#
# Example:
# ---sh
# yarn run build
# ---

# e: exit the script if any statement returns a non-true return value
# v: print shell input lines as they are read (including all comments!)
set -e

echo "Re/install project dependencies" \
&& composer config -g github-oauth.github.com $GH_TOKEN \
&& composer install --prefer-dist --no-interaction --no-suggest --verbose \
&& yarn install --non-interactive \
&& yarn run build \
&& echo "Re/install complete"
