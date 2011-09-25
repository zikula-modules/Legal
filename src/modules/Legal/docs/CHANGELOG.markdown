2.0.1
-----
  * Added new document types
    * Legal notice (active per default)
    * Cancellation right (inactive per default)
    * General terms and conditions of trade (inactive per default)
  * Use arbitrary urls
    * Each document type can now be linked to a custom url
    * As soon as an url is given it will be used instead of the normal Legal templates
    * So you can now use any page you want for displaying and managing your legal data
  * Improvements and bugfixes for the user menu plugin
  * Minor fixes regarding error states consistency

2.0.0
-----
 * General
    * The module has been updated to work with Zikula Core version 1.3.0 and its new features, and will not work with earlier versions.
    * The manner in which a user is prompted for acceptance of policies has changed somewhat. Rather than being coded into the Users module, the Legal module now uses the new features of 1.3.0. The user experience has changed.
 * Affecting customization
    * Template extensions have all changed to '.tpl'
    * The name of the privacy policy template has changed from 'legal_users_privacy.tpl' to 'legal_users_privacypolicy.tpl'.
    * The template parameters 'start', 'end', 'separator', and 'class' are deprecated for the 'legaluserlinks' template function. Modify the new template instead.
    * The templates that are language-specific have changed their names, and now contain only the text of the policy they represent. The text is included into other templates. This allows the text of the policy to be reused in several different modes of display.
    * New templates have been added to include the policy text templates and display them for existing functions.
    * The template plugins provided by the module are now themselves template-driven, rather than generating HTML markup within their PHP code.
 * Under the hood
    * Rather than relying on the 'activated' status value on the Users module's user account record, the Legal module now stores the date and time of a user's acceptance of each policy on the user's record as attributes.
