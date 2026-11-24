-- ============================================================
-- COPIE E EXECUTE ESTE CÓDIGO NO SQL EDITOR DO SUPABASE
-- LIBERA ATUALIZAÇÃO (UPDATE) E MODIFICAÇÃO DE ALUNOS
-- ============================================================

DROP POLICY IF EXISTS "Permitir leitura anonima alunos" ON public.alunos;
DROP POLICY IF EXISTS "Permitir insercao alunos" ON public.alunos;
DROP POLICY IF EXISTS "Permitir leitura e escrita alunos" ON public.alunos;

-- Cria política com permissão total (SELECT, INSERT, UPDATE, DELETE)
CREATE POLICY "Permitir leitura e escrita alunos" 
ON public.alunos 
FOR ALL 
USING (true) 
WITH CHECK (true);
