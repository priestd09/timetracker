# timetracker (tt) #

Yet another command-line time tracking app. Example usage:

    $ tt start someproject
    starting work on someproject at 08:32 on 05 May 2014

    $ tt
    working on someproject:
      from     08:32 on 05 May 2014
      to now,  08:57 on 05 May 2014
            => 0h25m have elapsed

    $ tt stop
    worked on someproject:
      from    08:32 on 05 May 2014
      to now, 09:37 on 05 May 2014
           => 1h5m elapsed

    $ tt summary
    someproject: 1h5m

    $ cat ~/.timetracker
    someproject: 08:32 on 05 May 2014 - 09:37 on 05 May 2014

    $ tt print
    someproject: 08:32 on 05 May 2014 - 09:37 on 05 May 2014

    $ tt print | grep "Dec 2011" | tt parse


## Installation ##

To install (tt) run the following commands:

 * `git clone https://github.com/chrisfrazier0/timetracker.git`
 * `cd timetracker && rake install`


## Bonus ##

Enable tab completion for project names by putting the following in your .zshrc:

    function _completett {
        reply=(start stop summary print parse $(tt projects))
    }
    compctl -K _completett tt


## License ##

Copyright (c) 2014, Chris Frazier <chris@chrisfrazier.me>  
GNU LGPL license

