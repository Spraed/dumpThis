# dumpThis
This PHP console command reads from a MSSQL database and writes to a MySQL database

## Usage
php app/console spraed:debug [--source [SOURCE]] [--goal [GOAL]]

* --source defaults on 'mssql' in app/config/config.yml
doctrine:
    dbal:
        connections:
            mssql:

* --source defaults on 'mysql' in app/config/config.yml
doctrine:
    dbal:
        connections:
            mysql:

## Limitations
* It transfers no indexes
* It transfers no keys
* It writes only in varchar(225)
