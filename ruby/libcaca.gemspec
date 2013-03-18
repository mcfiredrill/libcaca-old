# -*- encoding: utf-8 -*-
lib = File.expand_path('../lib/', __FILE__)
$:.unshift lib unless $:.include?(lib)

require 'caca/version'

Gem::Specification.new do |s|
  s.name        = "libcaca-ruby"
  s.version     = Caca::VERSION
  s.required_rubygems_version = Gem::Requirement.new(">= 0") if s.respond_to? :required_rubygems_version=
  s.authors     = [
    "Tony Miller",
    "Pascal Terjan",
    "Sam Hocevar"
  ]
  s.licenses    = ["WTFPL"]
  s.email       = ["mcfiredrill@gmail.com"]
  s.homepage    = "http://caca.zoy.org/"
  s.summary     = "ruby bindings for libcaca"
  s.description = "libcaca is a graphics library that outputs text instead of pixels."

  s.extensions  = ["ext/caca/extconf.rb"]

  s.rubyforge_project = "libcaca-ruby"

  s.files        = Dir.glob("lib/**/*.rb") +
                   Dir.glob("ext/**/*.{c,h,rb}") +
                   %w(README ruby.dox ruby-caca.dox)
  s.test_files   = Dir.glob("t/*") << "test"
  s.require_path = "lib"
end
