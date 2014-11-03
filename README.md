PHP Interpreter
===============

A PHP Interpreter, useful for evaluating simple PHP commands.

Warnings
--------
This utility grants access to all enabled functions in the PHP system to which it is deployed **to anyone who can access it**. For environments exposed to the internet (production and development), this could include anyone who can find or guess the link to this utility on your server. At the core of this utility is the [eval()](http://uk.php.net/eval) function, which allows the the execution of theoretically any PHP code - even code that may access and manipulate files __outside of the document root__, and execution of CLI code using backticks (i.e. `` `ls /` ``).

Because of this, we **strongly** recommend the following:-
 * __NEVER__ deploy this utility to production environments, even during development testing
 * __Do not__ deploy this utility to development environments accessible from the internet
 * It should be safe to deploy this utility to a locally-hosted web server (i.e. XAMPP), but only where you are certain the web server is not exposed to the internet

Please ensure you have read the [licence](LICENSE) corresponding to this project before deploying it - the author accepts no responsibility for misuse of the utility, including ignorance of the above warnings and the dangers of eval().

Usage
-----
Simply place phpi.php in the location where you would like to access it (obviously, this must be on the document root of a web server with PHP installed, or some localised variant (like [XAMPP](https://www.apachefriends.org/index.html)).

On load, the utility will be in single-line mode - to change between it and multi-line mode, click the red arrow to the right of the command-line.

In single-line mode, the *Enter* key will execute the entered code, and pressing the *Up* key will fill the command-line with the last command executed.

In multi-line mode, the *Enter* key will insert a new line, the *Tab* key will indent the current line (insert 4 spaces). In multi-line mode *Ctrl+Enter* will execute the entered code, and pressing *Ctrl+Up* will fill the command-line with the last command executed.

When an error is encountered, it will report the error in the output feed, and will leave the entered code in the command-line, awaiting corrections. For further information, the full error details (including trace) are reported to the console.

Author
------
php-interpreter (phpi) is written by [Richard Chaffer](http://richardchaffer.name).

Licence
-------
Copyright (c) 2014 Richard Chaffer
All Rights Reserved.
Released under the [MIT Licence](LICENSE).
