<?php

/**
 * Base View class
 *
 * Represents a View to be displayed.
 *
 * @version DR1.6.1
 */
class View implements IteratorAggregate {



	/** Dependant views accessible via Iterator interface */
	public $children = array();

	/** List of accepted events, all events when empty */
	protected $acceptedEvents = array();

	/** Event serializer object */
	protected $eventSerializer;

	/** Template engine object */
	protected $smarty;

	/** Special effect to use on update */
	protected $effect;

	/** Properties mapped to template variables */
	protected $data;

	/** Package name, template directory */
	protected $package = 'core-views';

	/** View identifier required */
	protected $idRequired = false;

	/** View is the root (topmost) in cascade */
	protected $isRootView = false;

	/** Markup element used to wrap element */
	protected $wrappingElement = 'span';



	public function __construct($id = null) {
		$this->id = trim($id);
		if (is_numeric($this->id)) {
			throw new Exception("view identifiers cannot be numeric ({$this->id})");
		}
		if ($this->idRequired and !$this->id) {
			throw new Exception("not null identifier required for this view");
		}
		$this->class = get_class($this);
		$this->template = strtolower(get_class($this));
		//$this->eventSerializer = new EventSerializer();
	}



	public function add(View $child, $alias = null) {
		$id = $alias ? $alias : $child->getId();
		if ($id) {
    		$this->children[] = $child;
			$this->$id = $child;
        } else {
		    $this->children[] = $child;
		}
	}
	


	public function addListener(Listener $listener) {
		/*
		if (!empty($this->acceptedEvents) and !in_array($listener->getEvent(), $this->acceptedEvents)) {
			throw new Exception('listener ' . get_class($listener) . ' not allowed for view ' . get_class($this));
		}
		$this->eventSerializer->addListener($listener);
		*/
	}



	public function getId() {
		return $this->id;
	}



	public function getIterator() {
 		return new ArrayIterator($this->children);
	}



	/**
	 * Sets visual effect on view update
	 *
	 * @param IVisualEffect
	 * @since DR1.5
	 */
	public function setEffect(IVisualEffect $effect) {
		$this->effect = $effect;
	}



	public function update() {
		/*
		if ($this->effect) {
			$this->effect->run($this);
		} else {
			$response = ResponseContext::getInstance();
			$response->update($this);
		}
		*/
	}



	public function __get($property) {
		if (isset($this->data[$property])) {
			return $this->data[$property];
		}
	}



	public function __set($property, $value) {
		$this->data[$property] = $value;
	}



	public function __isset($property) {
		return array_key_exists($property, $this->data);
	}



	public function __unset($property) {
		if (array_key_exists($property, $this->data)) {
			unset($this->data[$property]);
		}
	}



	public function setProperties($properties) {
		foreach ($properties as $property=>$value) {
			$this->data[$property] = $value;
		}
	}



	public function hasChildren() {
		return !empty($this->children);
	}



	public function getFamily($filtered = true) {
		$family = array();
		if ($filtered) {
			$iterator = new ViewFilterIterator($this->getIterator());
		} else {
			$iterator = $this->getIterator();
		}
		foreach ($iterator as $id => $child) {
			$family[$id] = $child;
			if ($child->hasChildren()) {
				$family = array_merge($family, $child->getFamily($filtered));
			}
		}
		return $family;
	}



    public function getChildById($id) {
        foreach (new ViewFilterIterator($this->getIterator()) as $k => $child) {
            if ($child->getId() == $id or $k == $id) {
                return $child;
            }
        }
        foreach (new ViewFilterIterator($this->getIterator()) as $child) {
            if ($sub = $child->getChildById($id)) {
                return $sub;
            }
        }
    }



	public function isRootView() {
		return $this->isRootView;
	}



	public function getWrappingElement() {
		return $this->wrappingElement;
	}



	protected function buildTemplateEngine() {}



	public function render() {
		return display_mustache_template($this->template, $this, False);
	}
	
	

	public function __toString() {
		return display_mustache_template($this->template, $this, False);
	}



} // class View

?>