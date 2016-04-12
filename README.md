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
- empty default config file (best feature IMO)


TODO:
- high prio
  - document the cryptFinder and its use

- low prio
  - enable callbacks for matching
  - somehow wrap output of CryptJsonType fields to include sth. like

`echo (!empty($this->cj_data) && is_array($this->cj_data)) ? json_encode($this->cj_data, JSON_UNESCAPED_UNICODE) : '';`
