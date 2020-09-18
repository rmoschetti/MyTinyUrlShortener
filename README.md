# MyTinyUrlShortener
A one-file DB-less url shortener written in php

I was tired to always loog for another third party url shortener, so I have written my own in PHP.
It consists in just one file (mtus.php) with create a auxiliary data file. The two possible urls are the following:

mtus.php?o=login - opens the login console, this opens the administration interface in which you can add / remove urls
mtus.php?u=mystring - redirect to the url saved under the name mystring. It also increment by one the number under "clicks"


The configuration data are at the beginning of mtus.php:

define('_FileDataName_',"mtus_data.php"); - This is the name of the auxiliary file. The extension is irrelevant, but I like to keep it php, which together with the first line of the file, specified in the following option, helps keeping the urls private.

define('_FileDataHeader_',"<?php exit(); ?>\n"); - This is the first line of the auxiliary file. It must contain at least "\n" at the moment (the parsing of the url starts always from the second line).

define('_Password_','hash_value'); - This is a password which must be inserted any time an operation with the data is performed. Notice that no session data are stored. I don't like for my password to be clearly stored in the code, so here is the hash made with password_hash function of PHP with algorithm PASSWORD_DEFAULT.

define('_PasswordHash_',true); - If it don't bother you to write a clear password in the code, just set this to false and write your password directly in _Password_. 

define('_FallbackUrl_',""); - This is a fallback url, in case one want to keep this code invisible. For instance, I use to rename mtus.php to index.php, and then use this option to redirect the user at the usual homepage of my website.