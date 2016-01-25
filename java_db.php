<?php  if (!defined('_VALID_BBC')) exit('No direct script access allowed');

if (!empty($_POST['submit']))
{
	if (!empty($_POST['class']) && !empty($_POST['fields']))
	{
		$class  = ucwords(trim($_POST['class']));
		$table  = !empty($_POST['table']) ? strtolower(trim($_POST['table'])) : strtolower($class);
		$fields = array_map('trim', explode(',', $_POST['fields']));
		if (count($fields) == 1)
		{
			$r = $db->getCol("SHOW FIELDS FROM ".$fields[0]);
			if (!empty($r))
			{
				$fields = $r;
			}
		}
		$colums = implode(', ', $fields);
		$vars   = '';
		$args   = '';
		$vals   = '';
		$i      = 0;
		foreach ($fields as $field)
		{
			$vars .= '
    public static final String '.$field.' = "'.$field.'";';
    	if ($i)
    	{
		    $args .= 'String _'.$field.', ';
		    $vals .= '
        cv.put('.$field.', _'.$field.');';
    	}
      $i++;
		}
		$args = trim(trim($args), ',');
		$txt  = '
import android.content.ContentValues;
import android.content.Context;
import android.database.sqlite.SQLiteDatabase;

/**
 * Created by danang on '.date('n/j/y').'.
 */
public class '.$class.' extends DBHelper {

    private static final int DB_VERSION = 1;
    public static final String table = "'.$table.'";'.$vars.'

    public '.$class.'(Context context) {
    		super(context, DB_VERSION, table);
    }

    public String[] getColumn() {
        return new String[]{'.$colums.'};
    }

    public boolean Insert('.$args.') {
        ContentValues cv = new ContentValues();'.$vals.'
        return Insert(cv);
    }

    @Override
    public void onCreate(SQLiteDatabase db) {
        db.execSQL("CREATE TABLE IF NOT EXISTS \'" + table + "\' (" + getSQLFormat() + ")");
    }
    @Override
    public void onUpgrade(SQLiteDatabase db, int oldVersion, int newVersion) {
        db.execSQL("DROP TABLE IF EXISTS " + table);
        db.execSQL("CREATE TABLE IF NOT EXISTS \'" + table + "\' (" + getSQLFormat() + ")");
    }
}
';
		?>
		<div class="form-group">
			<textarea class="form-control" onclick="this.select();"><?php echo $txt; ?></textarea>
		</div>
		<?php
	}else{
		echo msg('please Insert Class Name and Fields Name', 'danger');
	}
}


$sys->stop(false);
$sys->set_layout('blank');
?>
<form action="" method="POST" role="form">
	<legend>Create JAVA SQLite</legend>
	<div class="form-group">
		<label for="">Class Name</label>
		<input type="text" class="form-control" name="class" value="<?php echo @$_POST['class']; ?>" placeholder="Class Name">
	</div>
	<div class="form-group">
		<label for="">Fields Name / tablename of your database</label>
		<textarea name="fields" class="form-control" placeholder="Fields Name (comma delimiter) or just insert your table name in MySQL"><?php echo @$_POST['fields']; ?></textarea>
	</div>
	<div class="form-group">
		<label for="">Table Name</label>
		<input type="text" class="form-control" name="table" value="<?php echo @$_POST['table']; ?>" placeholder="Table Name">
		<div class="block-help">Leave it blank if it is the same as Class Name</div>
	</div>
	<button type="submit" name="submit" value="submit" class="btn btn-primary">Create Class</button>
</form>