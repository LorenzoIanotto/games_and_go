SELECT name, surname, summary, description, rating, AVG(rating) as average
FROM UserFeedback
JOIN User ON User.id=UserFeedback.customer_id
WHERE product_id=?
