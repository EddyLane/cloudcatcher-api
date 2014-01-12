#OVERRIDE THIS FOR EVERY PROJECT
$project_name = 'angular-symfony-stripe'

$project_dir = File.dirname(__FILE__)

$LOAD_PATH.unshift(ENV['BUILD_TOOLS'])

load "rake/database.rb"
load "rake/dependencies.rb"
load "rake/filesystem.rb"
load "rake/host.rb"
load "rake/testing.rb"
load "rake/uvd.rb"
load "rake/workflow.rb"


#set common workflow tasks here:
#task :build_dev => ?
#task :build_test => ?
#task :build_prod => ?
#task :build_ci => ?

task :build_dev => ["host:init", "symfony_build:dev"]
task :build_test => ["host:init", "symfony_build:test"]
task :build_prod => ["host:init", "symfony_build:prod"]
task :build_ci => ["host:init", "symfony_build:ci"]

