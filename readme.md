# database : A library to make database interactions easier
## Supported Databases:
1. MariaDB
2. MySQL

## Getting Started
### Installation
`composer require optiwariindia/database`

### Geting Started
*Prerequsites:*
- Database Hostname, generally localhost if database is installed on webserver.
- Database Username, usually root for local installations
- Database Password, usually blank for local installations
- Database Name, Generally same as hosting account name

create object of database class
`use optiwariindia\database`

`$db=new database([
    "host"=>"localhost",
    "user"=>"root", //Your Database user
    "pass"=>"", // Your database password
    "name"=>"{Database Name}" //Your Database Name
])`

### Basic Operations
#### Insert
Create an array of the data you want to store in database, set keys as name of fields for example Table auth, contains username and password only, then it shall be written like :
`$data=[`
    `"user"=>{Username},`
    `"pass"=>{Password}`
`];`

And then it can be inserted into auth table like:

`$db->insert("auth",$data);`
#### Select
The database class is providing 4 modes for selecting data as follows:
- mode 0 returns array with following keys:
    * fields: Array showing list of fields
    * rows: number of rows
    * touples with numeric indicies i.e. $data[0] shows first row.
- mode 1 returns rows as array containing filed names as key for each row.
- mode 2 returns id as row index and each row as collection of fields where field names are keys
- mode 3 returns same as mode 1 but parses all json arrays and eliminates all blobs.
By default mode 0 is selected in the object. To change mode you can use following: 
`$db->mode({mode number})`
To select Data from any table you can use following:
` $data= $db->select({table name},{filelds (optional, * by default)},{clauses})`
#### Update
The update function can be used to update data in a table. to update data create an array with filedname as keys like:
`$data=[`
    `"{field to be updated}"=>"{filed value}"`
`];`
and then 
`$db->update({table name},$data,{clause if any (Optional)});`
#### Delete
`$db->delete({tablename},{clause if any (optional)});`