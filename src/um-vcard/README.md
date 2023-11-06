## Ultimate Member - VCard

Adds a predefined field to generate VCard for users to download from their profiles.

Once the plugin is installed, you can now add the predefined field `VCard` to the Profile form. When a user saved the profile, it will generate the VCard.vcf file and then a VCard icon will be displayed in the Profile for users to download the file.

## Action hook

You can use the action hook `um_vcard_before_save` to add extra fields to the VCard. Here's an example:

```
add_action('um_vcard_before_save','um_vcard_add_nickname', 10, 2 );
function um_vcard_add_nickname( $vcard_obj, $user_id ){
   $vcard_obj->add( new Nickname( um_user('nickname' ) ) );
}
```

For more details to extend the VCard, please see this test class in the link:
https://github.com/jeroendesloovere/vcard/blob/2.0.0-dev/tests/VCardTest.php

## License

GNU Version 2 or Any Later Version
