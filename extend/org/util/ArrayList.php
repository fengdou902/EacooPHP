<?php
namespace org\util;

/**
 * ArrayList实现类
 * @author    liu21st <liu21st@gmail.com>
 */
class ArrayList implements \IteratorAggregate
{
    /**
     * 集合元素
     * @var array
     * @access protected
     */
    protected $elements = [];

    /**
     * 架构函数
     * @access public
     * @param string $elements  初始化数组元素
     */
    public function __construct($elements = [])
    {
        if (!empty($elements)) {
            $this->elements = $elements;
        }
    }

    /**
     * 若要获得迭代因子，通过getIterator方法实现
     * @access public
     * @return ArrayObject
     */
    public function getIterator()
    {
        return new \ArrayObject($this->elements);
    }

    /**
     * 增加元素
     * @access public
     * @param mixed $element  要添加的元素
     * @return boolean
     */
    public function add($element)
    {
        return (array_push($this->elements, $element)) ? true : false;
    }

    // 在数组开头插入一个单元
    public function unshift($element)
    {
        return (array_unshift($this->elements, $element)) ? true : false;
    }

    // 将数组最后一个单元弹出（出栈）
    public function pop()
    {
        return array_pop($this->elements);
    }

    /**
     * 增加元素列表
     * @access public
     * @param ArrayList $list  元素列表
     * @return boolean
     */
    public function addAll($list)
    {
        $before = $this->size();
        foreach ($list as $element) {
            $this->add($element);
        }
        $after = $this->size();
        return ($before < $after);
    }

    /**
     * 清除所有元素
     * @access public
     */
    public function clear()
    {
        $this->elements = [];
    }

    /**
     * 是否包含某个元素
     * @access public
     * @param mixed $element  查找元素
     * @return string
     */
    public function contains($element)
    {
        return (array_search($element, $this->elements) !== false);
    }

    /**
     * 根据索引取得元素
     * @access public
     * @param integer $index 索引
     * @return mixed
     */
    public function get($index)
    {
        return $this->elements[$index];
    }

    /**
     * 查找匹配元素，并返回第一个元素所在位置
     * 注意 可能存在0的索引位置 因此要用===False来判断查找失败
     * @access public
     * @param mixed $element  查找元素
     * @return integer
     */
    public function indexOf($element)
    {
        return array_search($element, $this->elements);
    }

    /**
     * 判断元素是否为空
     * @access public
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->elements);
    }

    /**
     * 最后一个匹配的元素位置
     * @access public
     * @param mixed $element  查找元素
     * @return integer
     */
    public function lastIndexOf($element)
    {
        for ($i = (count($this->elements) - 1); $i > 0; $i--) {
            if ($this->get($i) == $element) {
                return $i;
            }
        }
    }

    public function toJson()
    {
        return json_encode($this->elements);
    }

    /**
     * 根据索引移除元素
     * 返回被移除的元素
     * @access public
     * @param integer $index 索引
     * @return mixed
     */
    public function remove($index)
    {
        $element = $this->get($index);
        if (!is_null($element)) {
            array_splice($this->elements, $index, 1);
        }
        return $element;
    }

    /**
     * 移出一定范围的数组列表
     * @access public
     * @param integer $offset  开始移除位置
     * @param integer $length  移除长度
     */
    public function removeRange($offset, $length)
    {
        array_splice($this->elements, $offset, $length);
    }

    /**
     * 移出重复的值
     * @access public
     */
    public function unique()
    {
        $this->elements = array_unique($this->elements);
    }

    /**
     * 取出一定范围的数组列表
     * @access public
     * @param integer $offset  开始位置
     * @param integer $length  长度
     */
    public function range($offset, $length = null)
    {
        return array_slice($this->elements, $offset, $length);
    }

    /**
     * 设置列表元素
     * 返回修改之前的值
     * @access public
     * @param integer $index 索引
     * @param mixed $element  元素
     * @return mixed
     */
    public function set($index, $element)
    {
        $previous                = $this->get($index);
        $this->elements[$index]  = $element;
        return $previous;
    }

    /**
     * 获取列表长度
     * @access public
     * @return integer
     */
    public function size()
    {
        return count($this->elements);
    }

    /**
     * 转换成数组
     * @access public
     * @return array
     */
    public function toArray()
    {
        return $this->elements;
    }

    // 列表排序
    public function ksort()
    {
        ksort($this->elements);
    }

    // 列表排序
    public function asort()
    {
        asort($this->elements);
    }

    // 逆向排序
    public function rsort()
    {
        rsort($this->elements);
    }

    // 自然排序
    public function natsort()
    {
        natsort($this->elements);
    }

}