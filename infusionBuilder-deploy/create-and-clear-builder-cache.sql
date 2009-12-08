use build_cache;
create table if not exists cache(id VARCHAR(500) NOT NULL, counter INT NOT NULL DEFAULT 0,  UNIQUE ID(id) );\
delete from cache;
