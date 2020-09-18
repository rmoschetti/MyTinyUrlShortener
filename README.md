# MyTinyUrlShortener
A one-file DB-less url shortener written in php

I was tired to always loog for another third party url shortener, so I have written my own in PHP.
It consists in just one file (mtus.php) with create a auxiliary data file. The configuration data are at the beginning of mtus.php, the commands are the following

mtus.php?o=login - opens the login console, this opens the administration interface in which you can add / remove urls
mtus.php?u=mystring - redirect to the url saved under the name mystring. It also increment by one the number under "clicks"
