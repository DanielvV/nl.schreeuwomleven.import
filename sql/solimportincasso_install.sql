create table sol_import_incasso(
  id                    int(11) NOT NULL PRIMARY KEY ,
  status                varchar(1)  DEFAULT 'N' ,
  financial_type_id     varchar(10)  ,
  contact_id            varchar(14)  ,
  frequency_interval    int(11)      ,
  amount                int(11)      ,
  start_date            date         ,
  next_sched_contribution_date  varchar(8) ,
  note                  varchar(50)
)
