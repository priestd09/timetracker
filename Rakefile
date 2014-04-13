require 'rake'

desc "Hook the timetracker (tt) script into system-standard positions."
task :install do
  puts "Ensuring script is executable..."
  puts "  > chmod +x $PWD/tt.php"
  `chmod +x $PWD/tt.php`

  puts 'Linking in the script...'
  puts "  > sudo ln -s $PWD/tt.php /usr/local/bin/tt"
  `sudo ln -s $PWD/tt.php /usr/local/bin/tt`
end

task :uninstall do
  puts "Removing link..."
  puts "  > sudo rm -f /usr/local/bin/tt"
  `sudo rm -f /usr/local/bin/tt`

  puts "Removing timetracker db file..."
  puts "  > rm -f ~/.timetracker"
  `rm -f ~/.timetracker`
end

task :default => 'install'