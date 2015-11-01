delimiter |

CREATE PROCEDURE WITH_EMULATOR(
recursive_table varchar(100), # name of recursive table
initial_SELECT varchar(65530), # seed a.k.a. anchor
recursive_SELECT varchar(65530), # recursive member
final_SELECT varchar(65530), # final SELECT on UNION result
max_recursion int unsigned, # safety against infinite loop, use 0 for default
create_table_options varchar(65530) # you can add CREATE-TABLE-time options
# to your recursive_table, to speed up initial/recursive/final SELECTs; example:
# "(KEY(some_column)) ENGINE=MEMORY"
)

BEGIN
  declare new_rows int unsigned;
  declare show_progress int default 0; # set to 1 to trace/debug execution
  declare recursive_table_next varchar(120);
  declare recursive_table_union varchar(120);
  declare recursive_table_tmp varchar(120);
  set recursive_table_next  = concat(recursive_table, "_next");
  set recursive_table_union = concat(recursive_table, "_union");
  set recursive_table_tmp   = concat(recursive_table, "_tmp");
  # Cleanup any previous failed runs
  SET @str =
    CONCAT("DROP TEMPORARY TABLE IF EXISTS ", recursive_table, ",",
    recursive_table_next, ",", recursive_table_union,
    ",", recursive_table_tmp);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
 # If you need to reference recursive_table more than
  # once in recursive_SELECT, remove the TEMPORARY word.
  SET @str = # create and fill T0
    CONCAT("CREATE TEMPORARY TABLE ", recursive_table, " ",
    create_table_options, " AS ", initial_SELECT);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  SET @str = # create U
    CONCAT("CREATE TEMPORARY TABLE ", recursive_table_union, " LIKE ", recursive_table);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  SET @str = # create T1
    CONCAT("CREATE TEMPORARY TABLE ", recursive_table_next, " LIKE ", recursive_table);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  if max_recursion = 0 then
    set max_recursion = 100; # a default to protect the innocent
  end if;
  recursion: repeat
    # add T0 to U (this is always UNION ALL)
    SET @str =
      CONCAT("INSERT INTO ", recursive_table_union, " SELECT * FROM ", recursive_table);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    # we are done if max depth reached
    set max_recursion = max_recursion - 1;
    if not max_recursion then
      if show_progress then
        select concat("max recursion exceeded");
      end if;
      leave recursion;
    end if;
    # fill T1 by applying the recursive SELECT on T0
    SET @str =
      CONCAT("INSERT INTO ", recursive_table_next, " ", recursive_SELECT);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    # we are done if no rows in T1
    select row_count() into new_rows;
    if show_progress then
      select concat(new_rows, " new rows found");
    end if;
    if not new_rows then
      leave recursion;
    end if;
    # Prepare next iteration:
    # T1 becomes T0, to be the source of next run of recursive_SELECT,
    # T0 is recycled to be T1.
    SET @str =
      CONCAT("ALTER TABLE ", recursive_table, " RENAME ", recursive_table_tmp);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    # we use ALTER TABLE RENAME because RENAME TABLE does not support temp tables
    SET @str =
      CONCAT("ALTER TABLE ", recursive_table_next, " RENAME ", recursive_table);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    SET @str =
      CONCAT("ALTER TABLE ", recursive_table_tmp, " RENAME ", recursive_table_next);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
    # empty T1
    SET @str =
      CONCAT("TRUNCATE TABLE ", recursive_table_next);
    PREPARE stmt FROM @str;
    EXECUTE stmt;
  until 0 end repeat;
  # eliminate T0 and T1
  SET @str =
    CONCAT("DROP TEMPORARY TABLE ", recursive_table_next, ", ", recursive_table);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  # Final (output) SELECT uses recursive_table name
  SET @str =
    CONCAT("ALTER TABLE ", recursive_table_union, " RENAME ", recursive_table);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  # Run final SELECT on UNION
  SET @str = final_SELECT;
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  # No temporary tables may survive:
  SET @str =
    CONCAT("DROP TEMPORARY TABLE ", recursive_table);
  PREPARE stmt FROM @str;
  EXECUTE stmt;
  # We are done :-)
END|

delimiter ;