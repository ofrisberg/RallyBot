# RallyBot  
Messenger bot for Rebusrallyt in Uppsala (https://rally.utn.se/sv).  
Animation from the latest race: https://www.youtube.com/watch?v=9FeakEfJxWc  

## Requirements  
- PHP
- SQL-database
- Facebook app
- Facebook page with the Facebook app installed

## Usage  
See https://rally.utn.se/sv/rebusrallyt/boten-anna for latest version.  

## Files  
Below is a list of the source files with a brief description of what they do.  
The files you will make the most changes to is **config.ini**, **webhook.php** and **class.logic.php**.  

root/  
- **webhook.php**: The main running program. The webhook that Facebook make a request to on new messages.
- **config.ini**: Holds most of the configuration options, all may not be used.
- **setup.php**: Controls which classes that should be autoloaded.
- **scripts/**: These files do one time stuff like generating start times, generating html-tables and importing the teams from a CSV-file. 
- **io.php**: Fast input/output replies if no other command was matched.  

root/classes/  
- **class.answer.php**: Create, read and update a "stålfråga".
- **class.logic.php**: The core controller that decides what to do depending on what the user wrote.
- **class.message.php**: Create and reads all raw-data-messages that is sent to the bot.
- **class.messenger.php**: Abstraction layer for the Facebook API.
- **class.progress.php**: Connection class for a Team and a Station.
- **class.slack.php**: Slack abstraction layer. Used when participaints want to contact rallykå.
- **class.station.php**: Holds information about a Station, like "hjälprebusar" and coordinates.
- **class.summary.php**: Used on the admin-page to view statistics about the race in realtime.
- **class.team.php**: Read and update all information about a Team.
- **class.teamresult.php**: Used after rallyt to get the result on the admin page. Child class to Team.
- **class.user.php**: Manages a Messenger user and the current state. May or may not be connected to a team.  

root/admin/  
- **lunch.php**: Check in and out teams for lunch.
- **messages.php**: Send bulk messages to Teams within a specific starting number interval.
- **results.php**: The scoreboard for best time.
- **team.php**: Control and view information about a team.  

There are some files missing in the list above because they were not used during the latest race or not worth mentioning.
