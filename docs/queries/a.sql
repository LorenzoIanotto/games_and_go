SELECT created_at AS Data, name AS Nome, surname AS Cognome, total_amount AS Totale, payment_method AS `Metodo di pagamento`
FROM User
JOIN CustomerOrder ON User.id=CustomerOrder.customer_id
WHERE created_at BETWEEN ? AND ? ORDER BY created_at ASC;
