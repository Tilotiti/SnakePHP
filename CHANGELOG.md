2013-03-11
==========
@author Pierre de Vesian
* Added CHANGELOG.md
* Added "flushSQL" function (in /lib/system/function/)

2013-12-11
==========
@author Fabrice Lazzarotto
1. Everywhere (/lib) : normalized documentation
2. **query** class 
	* now support : LEFT, RIGHT, INNER ; **outer** function simulates FULL OUTER JOIN (/lib/system/class/class.query.php)
	* "FROM (subquery)" now supported via **from** called with a *query* argument
	* with "flushSQL" : categorized cache
	* with debug : query timer (optional - see also config.php)
3. **lang** class : use local mail templates in priority (useful for DEV)
4. **page::active** method : allow to test URIs with hashes (#)
5. Plugins :
	* **csrf** can now be managed in 2 code lines
6. Various enhancements (PHP ending tags removed, etc.)
