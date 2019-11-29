<?php
/*ingresar los datos para el funcionamiento de la base de datos*/

$nombre_bd="reemplazarnombre";

 $usuario="user"
$contraseña="password";

$ip_server="localhost";










function getArraySQL($resultsql){

$rawdata = array(); 
$i=0; 
while
($row=mysqli_fetch_array($resultsql)) { 

$rawdata[$i] = $row; 
$i++;

} 
return $rawdata; 
}

$ip_server="localhost";

$creat_log="CREATE TABLE IF NOT EXISTS dbo.logTransacciones (
TipoTrn char(1), 
Tabla varchar(128), 
PK varchar(1000), 
Campo varchar(128), 
ValorOriginal varchar(1000), 
ValorNuevo varchar(1000), 
FechaTrn datetime, Usuario varchar(128));

IF NOT EXISTS (SELECT *                 FROM   sys.objects                 WHERE  [type] = 'TR'                AND    [name] ='dbo.trIUDITEM')

BEGIN

CREATE TRIGGER dbo.trIUDITEM ON Item_meli FOR INSERT, UPDATE, DELETE
AS 

DECLARE @bit int ,	
@field int ,	
@maxfield int ,	
@char int ,	
@fieldname varchar(128) ,	
@TableName varchar(128) ,	
@PKCols varchar(1000) ,	
@sql varchar(2000), 	
@UpdateDate varchar(21) ,	
@UserName varchar(128) ,	
@Type char(1) ,	
@PKSELECT varchar(1000)
	
SELECT @TableName = 'Item_meli' 


SELECT @UserName = system_user ,
@UpdateDate = convert(varchar(8), getdate(), 112) + 
' ' + 
convert(varchar(12), getdate(), 114)

SET NoCount ON 

-- Identificar que evento se esta ejecutando (Insert, Update o Delete) 
--en base a cursores especiales (inserted y deleted)
if exists (SELECT * FROM inserted) 
if exists (SELECT * FROM deleted) --Si es un update
SELECT @Type = 'U'
else --Si es un insert
SELECT @Type = 'I'
else --si es un delete
SELECT @Type = 'D'
	
-- Obtenemos la lista de columnas de los cursores
SELECT * INTO #ins FROM inserted
SELECT * INTO #del FROM deleted
	
-- Obtener las columnas de llave primaria
SELECT @PKCols = coalesce(@PKCols + ' and', ' on') + 
' i.' + 
c.COLUMN_NAME + ' = d.' + 
c.COLUMN_NAME
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS pk
JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE c
ON c.TABLE_NAME = pk.TABLE_NAME
AND c.CONSTRAINT_NAME = pk.CONSTRAINT_NAME
WHERE pk.TABLE_NAME = @TableName AND 
pk.CONSTRAINT_TYPE = 'PRIMARY KEY'
	
-- Obtener la llave primaria y columnas para la inserci�n en la tabla de auditoria
SELECT 
@PKSELECT = coalesce(@PKSelect+'+','') + 
'''<' + 
COLUMN_NAME + 
'=''+convert(varchar(100),coalesce(i.' + 
COLUMN_NAME +',d.' + 
COLUMN_NAME + '))+''>''' 
FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS pk 
JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE c
ON c.TABLE_NAME = pk.TABLE_NAME
AND c.CONSTRAINT_NAME = pk.CONSTRAINT_NAME
WHERE pk.TABLE_NAME = @TableName
AND CONSTRAINT_TYPE = 'PRIMARY KEY'
	
if @PKCols is null --<-- Este trigger solo funciona si la tabla tiene llave primaria
BEGIN
RAISERROR('no PK on table %s', 16, -1, @TableName)
RETURN
END
END";
-----------------------------------------------------------------
$test_log="--Loop para armar el query de inserción en la tabla de log. 
--Un registro por cada campo afectado.
SELECT 
@field = 0, 
@maxfield = max(ORDINAL_POSITION) 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = @TableName

	
while @field < @maxfield
BEGIN
SELECT @field = min(ORDINAL_POSITION) 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = @TableName and ORDINAL_POSITION > @field
SELECT @bit = (@field - 1 )% 8 + 1
SELECT @bit = power(2,@bit - 1)
SELECT @char = ((@field - 1) / 8) + 1
if substring(COLUMNS_UPDATED(),@char, 1) & @bit > 0 or @Type in ('I','D')
BEGIN
SELECT @fieldname = COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
	 WHERE TABLE_NAME = @TableName and ORDINAL_POSITION = @field
SELECT @sql = 'insert LogTransacciones (TipoTrn, Tabla, PK, Campo, ValorOriginal, ValorNuevo, FechaTrn, Usuario)'
SELECT @sql = @sql + 	' SELECT ''' + @Type + ''''
SELECT @sql = @sql + 	',''' + @TableName + ''''
SELECT @sql = @sql + 	',' + @PKSelect
SELECT @sql = @sql + 	',''' + @fieldname + ''''
SELECT @sql = @sql + 	',convert(varchar(1000),d.' + @fieldname + ')'
SELECT @sql = @sql + 	',convert(varchar(1000),i.' + @fieldname + ')'
SELECT @sql = @sql + 	',''' + @UpdateDate + ''''
SELECT @sql = @sql + 	',''' + @UserName + ''''
SELECT @sql = @sql + 	' from #ins i full outer join #del d'
SELECT @sql = @sql + 	@PKCols
SELECT @sql = @sql + 	' where i.' + @fieldname + ' <> d.' + @fieldname 
SELECT @sql = @sql + 	' or (i.' + @fieldname + ' is null and d.' + @fieldname + ' is not null)' 
SELECT @sql = @sql + 	' or (i.' + @fieldname + ' is not null and d.' + @fieldname + ' is null)' 
exec (@sql)
END
END
	 
SET NoCount OFF; 


SELECT * FROM Item_meli;

SELECT * FROM LogTransacciones; "

-----------------------------------------------------------


try {
    $bd_connection = new PDO
('mysql:host=$ip_server;dbname=$nombre_bd', $usuario, $contraseña);
$crear_log=$bd_connection->prepare ($creat_log);
$respuesta =$bd_connection->prepare($test_log);

$crear_log->execute();
$info_log_meli=$respuesta->execute();

$resultforjson=getArraySQL($info_log_meli);


$json_string= json_encode($resultforjson);
$file = 'items.json'; file_put_contents($file, $json_string);
}
catch (Exception $e) { 	

echo $e->getMessage(); 	

}

