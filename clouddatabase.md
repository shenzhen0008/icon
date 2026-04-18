+-------------+----------------------------+------------------------+
| SCHEMA_NAME | DEFAULT_CHARACTER_SET_NAME | DEFAULT_COLLATION_NAME |
+-------------+----------------------------+------------------------+
| bitcon      | utf8mb4                    | utf8mb4_unicode_ci     |
+-------------+----------------------------+------------------------+
+------------------------------+--------+--------------------+
| TABLE_NAME                   | ENGINE | TABLE_COLLATION    |
+------------------------------+--------+--------------------+
| balance_ledgers              | InnoDB | utf8mb4_unicode_ci |
| cache                        | InnoDB | utf8mb4_unicode_ci |
| cache_locks                  | InnoDB | utf8mb4_unicode_ci |
| daily_settlements            | InnoDB | utf8mb4_unicode_ci |
| exchange_metrics             | InnoDB | utf8mb4_unicode_ci |
| failed_jobs                  | InnoDB | utf8mb4_unicode_ci |
| help_items                   | InnoDB | utf8mb4_unicode_ci |
| help_item_translations       | InnoDB | utf8mb4_unicode_ci |
| home_display_settings        | InnoDB | utf8mb4_unicode_ci |
| jobs                         | InnoDB | utf8mb4_unicode_ci |
| job_batches                  | InnoDB | utf8mb4_unicode_ci |
| migrations                   | InnoDB | utf8mb4_unicode_ci |
| password_reset_tokens        | InnoDB | utf8mb4_unicode_ci |
| popup_campaigns              | InnoDB | utf8mb4_unicode_ci |
| popup_campaign_user          | InnoDB | utf8mb4_unicode_ci |
| popup_receipts               | InnoDB | utf8mb4_unicode_ci |
| positions                    | InnoDB | utf8mb4_unicode_ci |
| position_redemption_requests | InnoDB | utf8mb4_unicode_ci |
| products                     | InnoDB | utf8mb4_unicode_ci |
| product_daily_returns        | InnoDB | utf8mb4_unicode_ci |
| product_reservations         | InnoDB | utf8mb4_unicode_ci |
| product_translations         | InnoDB | utf8mb4_unicode_ci |
| recharge_payment_requests    | InnoDB | utf8mb4_unicode_ci |
| recharge_receivers           | InnoDB | utf8mb4_unicode_ci |
| referral_commission_records  | InnoDB | utf8mb4_unicode_ci |
| referral_commission_settings | InnoDB | utf8mb4_unicode_ci |
| sessions                     | InnoDB | utf8mb4_unicode_ci |
| users                        | InnoDB | utf8mb4_unicode_ci |
| withdrawal_requests          | InnoDB | utf8mb4_unicode_ci |
+------------------------------+--------+--------------------+
+----------------------+---------+
| Variable_name        | Value   |
+----------------------+---------+
| character_set_server | utf8mb4 |
+----------------------+---------+
+------------------+--------------------+
| Variable_name    | Value              |
+------------------+--------------------+
| collation_server | utf8mb4_general_ci |
+------------------+--------------------+
root@iZ7xv3im0r3fy60q5cb1rlZ:/www/wwwroot/bitcon.yunqueapp.com# 


Last login: Sat Apr 18 16:12:01 on ttys000
linke@lindeMacBook-Pro ~ % mysql -uroot -p -D icon_market -e "SELECT SCHEMA_NAME, DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='icon_market'; SELECT TABLE_NAME, ENGINE, TABLE_COLLATION FROM information_schema.TABLES WHERE TABLE_SCHEMA='icon_market' ORDER BY TABLE_NAME;"

Enter password: 
+-------------+----------------------------+------------------------+
| SCHEMA_NAME | DEFAULT_CHARACTER_SET_NAME | DEFAULT_COLLATION_NAME |
+-------------+----------------------------+------------------------+
| icon_market | utf8mb4                    | utf8mb4_unicode_ci     |
+-------------+----------------------------+------------------------+
+------------------------------+--------+--------------------+
| TABLE_NAME                   | ENGINE | TABLE_COLLATION    |
+------------------------------+--------+--------------------+
| balance_ledgers              | InnoDB | utf8mb4_unicode_ci |
| cache                        | InnoDB | utf8mb4_unicode_ci |
| cache_locks                  | InnoDB | utf8mb4_unicode_ci |
| daily_settlements            | InnoDB | utf8mb4_unicode_ci |
| exchange_metrics             | InnoDB | utf8mb4_unicode_ci |
| failed_jobs                  | InnoDB | utf8mb4_unicode_ci |
| help_items                   | InnoDB | utf8mb4_unicode_ci |
| help_item_translations       | InnoDB | utf8mb4_unicode_ci |
| home_display_settings        | InnoDB | utf8mb4_unicode_ci |
| jobs                         | InnoDB | utf8mb4_unicode_ci |
| job_batches                  | InnoDB | utf8mb4_unicode_ci |
| migrations                   | InnoDB | utf8mb4_unicode_ci |
| password_reset_tokens        | InnoDB | utf8mb4_unicode_ci |
| popup_campaigns              | InnoDB | utf8mb4_unicode_ci |
| popup_campaign_user          | InnoDB | utf8mb4_unicode_ci |
| popup_receipts               | InnoDB | utf8mb4_unicode_ci |
| positions                    | InnoDB | utf8mb4_unicode_ci |
| position_redemption_requests | InnoDB | utf8mb4_unicode_ci |
| products                     | InnoDB | utf8mb4_unicode_ci |
| product_daily_returns        | InnoDB | utf8mb4_unicode_ci |
| product_reservations         | InnoDB | utf8mb4_unicode_ci |
| product_translations         | InnoDB | utf8mb4_unicode_ci |
| recharge_payment_requests    | InnoDB | utf8mb4_unicode_ci |
| recharge_receivers           | InnoDB | utf8mb4_unicode_ci |
| referral_commission_records  | InnoDB | utf8mb4_unicode_ci |
| referral_commission_settings | InnoDB | utf8mb4_unicode_ci |
| sessions                     | InnoDB | utf8mb4_unicode_ci |
| users                        | InnoDB | utf8mb4_unicode_ci |
| user_bank_cards              | InnoDB | utf8mb4_unicode_ci |
| withdrawal_requests          | InnoDB | utf8mb4_unicode_ci |
+------------------------------+--------+--------------------+
linke@lindeMacBook-Pro ~ % mysql -uroot -p -D icon_market -e "SHOW VARIABLES LIKE 'character_set_server'; SHOW VARIABLES LIKE 'collation_server';"

Enter password: 
+----------------------+---------+
| Variable_name        | Value   |
+----------------------+---------+
| character_set_server | utf8mb4 |
+----------------------+---------+
+------------------+--------------------+
| Variable_name    | Value              |
+------------------+--------------------+
| collation_server | utf8mb4_general_ci |
+------------------+--------------------+
linke@lindeMacBook-Pro ~ % mysql -uroot -p -D icon_market -e "SHOW FULL COLUMNS FROM users;"

Enter password: 
+-------------------+---------------------+--------------------+------+-----+---------+----------------+---------------------------------+---------+
| Field             | Type                | Collation          | Null | Key | Default | Extra          | Privileges                      | Comment |
+-------------------+---------------------+--------------------+------+-----+---------+----------------+---------------------------------+---------+
| id                | bigint(20) unsigned | NULL               | NO   | PRI | NULL    | auto_increment | select,insert,update,references |         |
| username          | varchar(21)         | utf8mb4_bin        | NO   | UNI | NULL    |                | select,insert,update,references |         |
| name              | varchar(255)        | utf8mb4_unicode_ci | NO   |     | NULL    |                | select,insert,update,references |         |
| remark            | text                | utf8mb4_unicode_ci | YES  |     | NULL    |                | select,insert,update,references |         |
| email             | varchar(255)        | utf8mb4_unicode_ci | NO   | UNI | NULL    |                | select,insert,update,references |         |
| email_verified_at | timestamp           | NULL               | YES  |     | NULL    |                | select,insert,update,references |         |
| password          | varchar(255)        | utf8mb4_unicode_ci | NO   |     | NULL    |                | select,insert,update,references |         |
| balance           | decimal(16,2)       | NULL               | NO   |     | 0.00    |                | select,insert,update,references |         |
| invite_code       | varchar(32)         | utf8mb4_unicode_ci | YES  | UNI | NULL    |                | select,insert,update,references |         |
| referrer_id       | bigint(20) unsigned | NULL               | YES  | MUL | NULL    |                | select,insert,update,references |         |
| is_admin          | tinyint(1)          | NULL               | NO   | MUL | 0       |                | select,insert,update,references |         |
| remember_token    | varchar(100)        | utf8mb4_unicode_ci | YES  |     | NULL    |                | select,insert,update,references |         |
| created_at        | timestamp           | NULL               | YES  |     | NULL    |                | select,insert,update,references |         |
| updated_at        | timestamp           | NULL               | YES  |     | NULL    |                | select,insert,update,references |         |
+-------------------+---------------------+--------------------+------+-----+---------+----------------+---------------------------------+---------+
linke@lindeMacBook-Pro ~ % 
