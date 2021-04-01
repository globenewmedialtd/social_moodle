# social_moodle

## Installation
Install like any other drupal module
## Configuration

### Taxonomy

The module installs the following taxonomy vocabularies:

- social_moodle_enrollment_method
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

### Views

Please make sure that your group type/group content is enabled in the provided views:

- view.group_iterations
- view.manage_language_versions
- view.social_moodle_template_it
- view.social_moodle_template_lv


