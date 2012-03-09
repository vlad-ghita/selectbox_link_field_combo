Select Box Link Field Combo 
===========================

It allows creation of chained selects in Admin.

* Version: 1.1.1
* Build Date: 2012-03-09
* Authors:
	- [Vlad Ghita](http://www.xanderadvertising.com)
* Requirements:
	- Symphony 2.2.x
	- [Select Box Link Field](https://github.com/symphonycms/selectbox_link_field)

Thank you all other Symphony Extensions developers for your inspirational work.



## 1. Instalation ##

1. Upload the 'selectbox_link_field_combo' folder in this archive to your Symphony 'extensions' folder.
2. Enable it by selecting the "Field: Select Box Link Combo", choose Enable from the with-selected menu, then click Apply.
3. You can now add the "Select Box Link Combo" field to your sections.




## 2. Usage ##

- Works in an identical way to the standard `SBL` with some additions for parent relation.
- Setting an instance of the field to be not required will cause an empty option to show up on the publish form.
- Always the root of a chain will be a SBL.

The settings of Select Box Link Combo describe the behavior of the field. You will need to set the following:

- `Parent` of the select. It will be another SBL / SBLC from **current** section.
- `Values` the options which will populate the select.
- `Relation` an external SBL (from **another** section) describing relations between `Values` and `Parent`.



## 3. Example ##

SBL = Select Box Link<br />
SBLC = Select Box Link Combo

Lets say you have a `Persons` Section and each person has to be localized on the globe by Continent->Country->City. You would create the following sections to store the info:


Section `Continents`<br />
1. Text Input<br />
	- `name` : Title


<br />
Section `Countries`<br />
1. Text Input<br />
	- `name` : Title<br />
2. Select Box Link (SBL\#1)<br />
	- `name` : Continent<br />
	- `values` : Continents->Title


<br />
Section `Cities`<br />
1. Text Input<br />
	- `name` : Title<br />
2. Select Box Link (SBL\#2)<br />
	- `name` : Country<br />
	- `values` : Countries->Title


<br />
Section `Persons`<br />
1. Text Input<br />
	- `name` : Name<br />
2. Select Box Link (SBL\#3)<br />
	- `name` : Continent<br />
	- `values` : Continents->Title<br />
**SAVE the Section first. Next field needs SBL\#3's ID from Database**<br />
3. Select Box Link Combo (SBLC\#1)<br />
	- `name` : Country<br />
	- `parent` : Persons->Continent (SBL\#3)<br />
	- `values` : Countries->Title<br />
	- `relation` : Countries->Continent (SBL\#1)<br />
**SAVE the Section first. Next field needs SBLC\#1's ID from Database**<br />
4. Select Box Link Combo (SBLC\#2)<br />
	- `name` : City<br />
	- `parent` : Persons->Country (SBLC\#1)<br />
	- `values` : Cities->Title<br />
	- `relation` : Cities->Country (SBL\#2)

Enjoy!
