START TRANSACTION;

SET @deleted_id = (SELECT user_id FROM Employee WHERE code=?);
DELETE FROM Employee WHERE user_id=@deleted_id;
DELETE FROM `User` WHERE id=@deleted_id;

COMMIT;
