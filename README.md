#felixkempf/cake-crypt

- Database Types for encrypting string or json data.
- Finder method via Behavior to emulate
  - conditions
  - select
  - contain
  - matching
  - list
  - sort on not encrypted fields. Sorting encrypted fields may be done with `Collection::sort` or `Hash::sort`

- Component to translate pagination queries into finder options and provides helper method `CryptComponent::setPaginationSettings`
- BcryptPasswordHasher to guarantee a password hash long and secure enough to function as en-/decryption key. Default cost is 16
- not empty default config file with second best feature: auto-inclusion by bootstrapping when loading the Plugin
`Plugin::load('Crypt', [
    'bootstrap' => true,
    'routes' => false,
    'autoload' => true
]);`


TODO:
- high prio
  - either find a way around the plugin workaround or document how to do it
  - document the CryptBehavior and its use

- low prio
  - enable callbacks for matching
  - somehow wrap output of CryptJsonType fields to include sth. like

`echo (!empty($entity->cj_data) && is_array($entity->cj_data)) ? json_encode($entity->cj_data, JSON_UNESCAPED_UNICODE) : '';`
