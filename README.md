Translate yaml is a simple PHP script for translating your YAML files using Bing Translator.

### Dependencies

###### Install PHP-Pear and libYAML

```bash
$ sudo apt-get install php-pear libyaml-dev
$ sudo pecl install yaml-1.1.0
```

Now edit your php.ini file located at

```bash
$ php -i | grep "php.ini"
```
and add 
```
extension=yaml.so
```
Restart apache

```
sudo service apache2 restart
```
### Installation

In your <em>Terminal</em>:

   ```$ git clone https://github.com/carcabot/Yaml-to-Xlf-Translator.git```

And enter to directory:

   ```$ cd Yaml-to-Xlf-Translator/```
 
### Example

Make sure you have your translation files in e.g.:
```
  langs/en.yml (English)
  langs/de.yml (German)
  langs/es.yml (Spanish)
```
Run:

  ```bash
  $ php -q translate.php --from=en --to=es --client="<Your Microsoft API Application Client ID>" --secret="<Your Microsoft API Application Client Secret>"
  ```
  
This will create the following file:
```
  langs/es.yml
```  

Enjoy :-)

### Languages

* To view the list of supported languages run:

  ```bash
    php -q translate.php --languages
  ```

###### Creating Bing Application
To create bing application use this url https://datamarket.azure.com/developer/applications

## Thanks


Copyright (c) 2016, released under the MIT license
