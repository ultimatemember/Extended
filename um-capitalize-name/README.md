## Ultimate Member - Force Capitalization of Display Name( First and Last Names )

This forces the Display Name to be capitalized with special prepositions globally

-  Once this plugin is installed and activated, it will capitalize all display names in the UM Profile Form, Account Form and Member Directory
-  It supports special prepositions:
-  Some use cases:

| Original             | ucwords(strtolower(\$str) | UM Capitlize(\$str)  |
| :------------------- | :------------------------ | :------------------- |
| jOÃO DA SILVA        | João Da Silva             | João da Silva        |
| eduardo dall'agnoll  | Eduardo Dall'agnoll       | Eduardo Dall'Agnoll  |
| Papa joão xxiii      | Papa João Xxiii           | Papa João XXIII      |
| Google s/a           | Google S/a                | Google S/A           |
| paul mccartney       | Paul Mccartney            | Paul McCartney       |
| nome da empresa ltda | Nome Da Empresa Ltda      | Nome da Empresa LTDA |
| nome da empresa me   | Nome Da Empresa Me        | Nome da Empresa ME   |
| Mr. o'donnel         | Mr. O'donnel              | Mr. O'Donnel         |

## Performance

By default, this plugin capitlizes the UM Display Name on page load. If you are experiencing performance issues, you can disable this with the following code snippet:

```
add_filter("um_extended_capitalize_name_forced","__return_false");
```

If the above code is added, this capitalizes the first name, last name and display name on profile/account update. It also works when you create or update the account via WP Admin > Users.

## Display Name Column

This plugin adds an extra column to the WP Users List as the default Name has no filter hook to force the capitalization. If you want to disable this, use the following code snippets:

```
remove_filter( 'manage_users_custom_column', 'um_extended_capitalize_column_content', 10, 3 );
remove_filter( 'manage_users_columns', 'um_extended_add_display_name_column', 1 );
```

## Extend

**Word Splitters**

-  Filter Hook: `um_extended_capitlize_name__word_splitter`
-  Default values: `' ', '-', "O’", "L’", "D’", 'St.', 'Mc', "Dall'", "l’", "d’", "a’", "o’"`

**Lowercase Exceptions**

-  Filter Hook: `um_extended_capitlize_name__lowercase_exceptions`
-  Default values: `'the', 'van', 'den', 'von', 'und', 'der', 'da', 'of', 'and', "d’", 'das', 'do', 'dos', 'e', 'el`

**Lowercase Exceptions**

-  Filter Hook: `um_extended_capitlize_name__uppercase_exceptions`
-  Default values: `'III', 'IV', 'VI', 'VII', 'VIII', 'IX', 'ME', 'EIRELI', 'EPP', 'S/A', 'S.A', 'LTDA'`

## License

GNU Version 2 or Any Later Version
