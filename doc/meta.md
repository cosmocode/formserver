# General form settings (meta)

```yaml
meta:
  title: 'Form title'
  css: custom.css
  logo: /logo.png

```
As with all files, the slash indicates that the file is located in `public/`, otherwise the form directory is prepended.

Your CSS file will be included after our basic styles, which are mostly plain [Bulma](https://bulma.io/).  

## Mail options

```yaml
  email:
    subject: 'Status of {{fieldset1.widget1}}'
    recipients:
      - <email>
      - <email>
    cc:
      - fieldset2.email
```

An email with user input and attached uploads will be sent to configured addresses. A copy can be sent to an address from an email field if you provide the fieldId(s) in the `cc` section.   

## Translations

```yaml
  language: de
```

The set language will be loaded from ```/conf/language.<language>.yaml```.

At first ```/conf/language.default.yaml``` gets loaded.

If ```/conf/language.local.yaml``` exists it will override all set strings from the default file.

Finally ```/conf/language.<language>.yaml``` overrides all set strings from the previous files.


## File export options

```yaml
  export: file_to_be_exported.txt
```

When the form is successfully submitted the given file (in this case file_to_be_exported.txt) will be copied to the export directory. The exported file will be named the same as the config directory, aka form id. 

The export directory is set by default to ./export. It can be changed by overriding the default settings (./conf/settings.default.yaml):
 Create / Edit the file ./conf/settings.local.yaml.
 Add:
  ```yaml
   fileExporter:
     dir : <anotherFolder>
 ```
 
 The directory is always relative to the project's root directory.
 
 ## "Save" button
 
 ```yaml
   saveButton: false
 ```
 
This option disables not only the save button, but also persisting user inputs. **As soon as the form is successfully submitted, all data is lost, so send it per email!**

This way you can re-use the same form for multiple submissions of different data or for multiple users.

## Tooltips

 ```yaml
   tooltip_style: 'border: 1px solid cyan'
 ```

This setting controls the tooltip button style attribute.

Every tooltip for this form is affected.
