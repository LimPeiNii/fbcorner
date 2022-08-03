DELIMITER $$
CREATE FUNCTION today_opening_closing_time
(
 details TEXT, index_name TEXT
)
RETURNS TEXT DETERMINISTIC
BEGIN
    DECLARE array_index, result TEXT;
    SET array_index = CONCAT('$.',DAYNAME(NOW()),'.',index_name);
    SET result = JSON_EXTRACT(details, array_index);
    SET result = SUBSTRING_INDEX(result, '"', 2);
    SET result = SUBSTRING(result, 2);
    RETURN result;
END$$
DELIMITER ;