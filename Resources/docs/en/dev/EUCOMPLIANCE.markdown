EU COOKIE COMPLIANCE
--------------------

As of version 2.0.4, LegalModule now allows automatic display of an EU Cookie Compliance notice on your website.
This must be manually enabled in the LegalModule admin settings. LegalModule uses the JS Script from
http://www.primebox.co.uk/projects/cookie-bar/ in order to implement this. More information is available on their
website.

A cookie is set on the users browser to indicate assent. This cookie expires after one year.

Options:
  * Translation of notice and button text is available.
  * Override of stylesheet is allowed:
    Set a parameter value for `euwarning.stylesheet` in your `custom_parameters.yml` file and it will be used.
    Use full path from document root.
    Be sure to copy the existing stylesheet (`/Resources/public/js/jquery.cookiebar/jquery.cookiebar.css`)
  * Other JS options are not customizable at this time (pull requests welcome).

Developer Info
--------------

The JS and stylesheet are injected directly into the Response via an Event Listener (Listener/EuCookieWarningInjectorListener.php).
The code is not dependent on the renderer and so will operate with both Smarty and Twig.
The code is dependant on "well-formed" HTML as it searches for `</body>` and `</head>` tags.