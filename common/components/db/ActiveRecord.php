<?php
namespace common\components\db;

use dezmont765\yii2bundle\models\MainActiveRecord;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

/**
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 *
 * As an example, say that the `Customer` ActiveRecord class is associated with the `customer` table.
 * This would mean that the class's `name` attribute is automatically mapped to the `name` column in `customer` table.
 * Thanks to Active Record, assuming the variable `$customer` is an object of type `Customer`, to get the value of
 * the `name` column for the table row, you can use the expression `$customer->name`.
 * In this example, Active Record is providing an object-oriented interface for accessing data stored in the database.
 * But Active Record provides much more functionality than this.
 *
 * To declare an ActiveRecord class you need to extend [[\yii\db\ActiveRecord]] and
 * implement the `tableName` method:
 *
 * ```php
 * <?php
 *
 * class Customer extends \yii\db\ActiveRecord
 * {
 *     public static function tableName()
 *     {
 *         return 'customer';
 *     }
 * }
 * ```
 *
 * The `tableName` method only has to return the name of the database table associated with the class.
 *
 * > Tip: You may also use the [Gii code generator](guide:start-gii) to generate ActiveRecord classes from your
 * > database tables.
 *
 * Class instances are obtained in one of two ways:
 *
 * * Using the `new` operator to create a new, empty object
 * * Using a method to fetch an existing record (or records) from the database
 *
 * Here is a short teaser how working with an ActiveRecord looks like:
 *
 * ```php
 * $user = new User();
 * $user->name = 'Qiang';
 * $user->save();  // a new row is inserted into user table
 *
 * // the following will retrieve the user 'CeBe' from the database
 * $user = User::find()->where(['name' => 'CeBe'])->one();
 *
 * // this will get related records from orders table when relation is defined
 * $orders = $user->orders;
 * ```
 *
 * For more details and usage information on ActiveRecord, see the [guide article on
 * ActiveRecord](guide:db-active-record).
 *
 */
class ActiveRecord extends MainActiveRecord
{
    const CACHE_DURATION = 5;

    const PAGE_SIZE = 100;
    const PAGE_SIZE_LIMIT_MIN = 1;
    const PAGE_SIZE_LIMIT_MAX = 100;
    const ENABLE_MULTI_SORT = false;
    const SEARCH_LIMIT = 100;

    const SORT_PARAM = 'sort';
    const FILTER_PARAM = 'filter';
    const PAGE_PARAM = 'page';
    const PAGE_SIZE_PARAM = 'per_page';

    const PHONE_VALIDATION_PATTERN = '/^([+]?[0-9-\s()]+)$/';
    const POSTCODE_VALIDATION_PATTERN = '/^([0-9-\s()a-zA-Z]+)$/';
    const FAX_VALIDATION_PATTERN = '/^\+?[0-9-\s()]+$/';

    const YES = 1;
    const NO = 0;


    public function getUpdatedAttributes() {
        if($this->attributes == $this->oldAttributes) return [];
        foreach($this->attributes as $attribute => $value) {
            if(!array_key_exists($attribute, $this->oldAttributes) || $this->oldAttributes[$attribute] != $value) {
                $attributes[$attribute] = $attribute;
            }
        }
        $unnecessary_attributes = static::getListUnnecessaryAttributes();
        if($unnecessary_attributes != null) {
            foreach($attributes as $attribute => $value) {
                if(in_array($attribute, $unnecessary_attributes)) {
                    unset($attributes[$attribute]);
                }
            }
        }
        return array_combine($attributes, $attributes);
    }


    public static function getListUnnecessaryAttributes() {
        return [
            'created_at',
            'modified_at',
            'created_by',
            'modified_by',
        ];
    }


    public static function getListYesNo() {
        return [
            self::YES => Yii::t('common.common', 'Yes'),
            self::NO => Yii::t('common.common', 'No'),
        ];
    }


    public static function getCacheValue($key) {
        if($cache = Yii::$app->get('cache')) {
            return $cache->get($key);
        }
    }


    public static function setCacheValue($key, $value, array $tags, $duration = 0) {
        if($cache = Yii::$app->get('cache')) {
            return $cache->set($key, $value, $duration, new TagDependency(['tags' => $tags]));
        }
    }


    public static function deleteCacheValue(array $tags) {
        if($cache = Yii::$app->get('cache')) {
            return TagDependency::invalidate($cache, $tags);
        }
    }
}