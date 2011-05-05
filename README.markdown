Select Box Link Field Combo 
------------------------------------

Version: 1.0beta<br />
Author: [Vlad Ghita](vlad_micutul@yahoo.com)<br />
Build Date: 4th May 2011<br />
Requirements:<br />
  
- Symphony 2.2.1 or greater. Untested in previous versions.
- [Select Box Link Field](http://symphony-cms.com/download/extensions/view/20054/)

In short, it allows creation of chained select boxes in backend.

##INSTALLATION##

1. Upload the 'selectbox_link_field_combo' folder in this archive to your Symphony 'extensions' folder.

2. Enable it by selecting the "Field: Select Box Link Combo", choose Enable from the with-selected menu, then click Apply.

3. You can now add the "Select Box Link Combo" field to your sections.


##UPDATING##

1. Be sure to visit the Extension page in the Symphony admin and enable "Field: Select Box Link Combo" so the database is updated accordingly.


##USAGE##

SBL = Select Box Link<br />
SBLC = Select Box Link Combo

- Works in an identical way to the standard `select box link field` with some additions for parent relation.

- Setting an instance of the field to be not required will cause an empty option to show up on the publish form.

- Always the root of a chain will be a SBL.

The settings of Select Box Link Combo describe the behavior of the field. One will need to set the following:

- `Parent` of the select. It will be another SBL / SBLC from **current** section.
- `Values` the options which will populate the select.
- `Relation` an external SBL (from **another** section) describing relations between `Values` and `Parent`.

###EXAMPLE###

Lets say you have a `Persons` Section and each person has to be localized on the globe by Continent->Country->City. One would create the following sections to store the info:

Continents<br />
1. Text Input: Title

Countries<br />
1. Text Input: Title<br />
2. SBL\#1: Continent<br />
- `Values` : Continents-&gt;Title

Cities<br />
1. Text Input: Title<br />
2. SBL\#2: Country<br />
- `Values` : Countries-&gt;Title
  
Persons<br />
1. Text Input: Name<br />
2. SBL\#3: Continent<br />
- `Values` : Continents-&gt;Title<br />
**SAVE the Section first. Next field needs SBL\#3's ID from Database.**<br />
3. SBLC\#1: Country<br />
- `Parent` : Persons-&gt;Continent (SBL\#3)<br />
- `Values` : Countries-&gt;Title<br />
- `Relation` : Countries-&gt;Continent (SBL\#1)<br />
**SAVE the Section first. Next field needs SBLC\#1's ID from Database.**<br />
4. SBLC\#2: City<br />
- `Parent` : Persons-&gt;Country (SBLC1)<br />
- `Values` : Cities-&gt;Title<br />
- `Relation` : Cities-&gt;Country (SBL2)

Enjoy!

##CHANGE LOG##

1.0beta	- Initial release.