#!/bin/bash
cd "$(dirname "$0")"
/usr/bin/php run/run_index.php;
/usr/bin/php run/run_index_update.php;
/usr/bin/php run/run_index_delete.php;
