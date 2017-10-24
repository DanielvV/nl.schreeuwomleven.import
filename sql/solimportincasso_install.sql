create table sol_import_incasso(
  id                    INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  status                varchar(1)  DEFAULT 'N' ,
  financial_type_id     varchar(40)  ,
  contact_id            varchar(14)  ,
  frequency_interval    int(11)      ,
  amount                varchar(11)  ,
  start_date            date         ,
  DtOfSgntr             date         ,
  MndtId                varchar(40)  ,
  next_sched_contribution_date  date ,
  iban                  varchar(40)  ,
  account_holder        varchar(128) ,
  note                  varchar(50)
)
