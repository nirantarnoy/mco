-- SQL script to update company_id in purchase_master table
-- ry-qt -> company_id = 1
-- arc-qt -> company_id = 2

UPDATE purchase_master 
SET company_id = 1 
WHERE docnum LIKE 'ry-qt%' 
   OR refnum LIKE 'ry-qt%' 
   OR job_no LIKE 'ry-qt%';

UPDATE purchase_master 
SET company_id = 2 
WHERE docnum LIKE 'arc-qt%' 
   OR refnum LIKE 'arc-qt%' 
   OR job_no LIKE 'arc-qt%';

-- Verify updates
SELECT company_id, COUNT(*) 
FROM purchase_master 
WHERE docnum LIKE 'ry-qt%' OR docnum LIKE 'arc-qt%' 
   OR refnum LIKE 'ry-qt%' OR refnum LIKE 'arc-qt%'
GROUP BY company_id;
