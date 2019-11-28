In SugarCRM 6.5.x
Any layout changes will be in custom/modules/<the module>/metadata
Label changes are in custom/modules/<the module>/language

I use version control on my test and production servers so after testing I commit the changes to those files and when it's production approved I update the corresponding files in production.

Changes to dependencies, variable definitions etc are made in custom/Extensions/modules/<the module>/Ext/
Those are then moved to custom/modules/<the module>/Ext when you repair/rebuild.

New fields are a little harder, as those are database changes.
What I do is create fields using SQL entry statements in the fields_meta_data table and running a reapair/rebuild to create a field in the appropriate table. I do this instead of using Studio so I can reproduce in production exactly. 
You can either track your inserts in a spreadsheet or similar (I use JIRA to track all projects/changes on our sugar system) or you can use something like mySQLWorkbench to export that particular record and insert it in your production environment.
