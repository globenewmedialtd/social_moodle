# social_moodle

## Installation
Install like any other drupal module. If you have social_moodle already installed in your system please make sure you delete the module before you install the first RC Candiate.
Be also sure you have deleted the following taxonomies: Social Moodle Enrollment Method, Social Moodle Languages and Social Moodle Templates.

Be also sure you have deleted the following display modes: Iteration Application, Iteration listing.

Be also sure you have deleted the provided content types: Iteration and Language Version.

You only have to do that in case you have an older version of the module installed.


## Configuration

### Taxonomy

The module installs the following taxonomy vocabularies:

- social_moodle_languages
- social_moodle_templates

Please fill them with the desired terms, otherwise form fields referencing to these fields will be empty.

### Blocks

Please be sure you configure the visiblity page settings for social blocks like the "Group hero block"
- /group/*/language-versions
- /group/*/iterations


The module provides 2 additonal blocks, you want to place them into the "Complementary top" region:
- Group add language version block
- Group add iteration version block
The iteration listing you put into the "Complementary bottom" region:
- Iteration Block Listing (View Listing)

### Permissions
Please checkout the permissions. Go to section "Social Moodle Application" and decide which role you want 
grant the permissons for viewing and editing the application.

In the same section decide which role you want to grant the workflow permissions. Users can only use the worflow you allow!

Give the roles the Access application view. That permission is for all roles you want to grant the permission, the listing
of applications is defined by the application entity (edit, update, etc.) permissions.


### Views

Please make sure that your group type/group content is enabled in the provided views:

- view.group_iterations
- view.manage_language_versions
- view.social_moodle_template_it
- view.social_moodle_template_lv


