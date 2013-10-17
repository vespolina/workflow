<?php

namespace Vespolina\Workflow\Dumper;

use Vespolina\Workflow\Workflow;

/**
 * GraphvizDumper dumps a workflow as a graphviz file.
 *
 * You can convert the generated dot file with the dot utility (http://www.graphviz.org/):
 *
 *   dot -Tpng container.dot > foo.png
 *
 * @author Luis Cordova <cordoval@gmail.com>
 */
class GraphvizDumper
{
    private $workflow;
    private $nodes;
    private $edges;
    private $options = array(
        'graph' => array('ratio' => 'compress'),
        'node'  => array('fontsize' => 11, 'fontname' => 'Arial', 'shape' => 'record'),
        'edge'  => array('fontsize' => 9, 'fontname' => 'Arial', 'color' => 'grey', 'arrowhead' => 'open', 'arrowsize' => 0.5),
    );

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * Dumps the workflow as a graphviz graph.
     *
     * Available options:
     *
     *  * graph: The default options for the whole graph
     *  * node: The default options for nodes
     *  * edge: The default options for edges
     *
     * @param array $options An array of options
     *
     * @return string The dot representation of the service container
     */
    public function dump(array $options = array())
    {
        foreach (array('graph', 'node', 'edge') as $key) {
            if (isset($options[$key])) {
                $this->options[$key] = array_merge($this->options[$key], $options[$key]);
            }
        }

        $this->nodes = $this->findNodes();

        $this->edges = $this->findEdges();

        return $this->startDot().$this->addNodes().$this->addEdges().$this->endDot();
    }

    /**
     * Returns all nodes.
     *
     * @return string A string representation of all nodes
     */
    private function addNodes()
    {
        $code = '';
        foreach ($this->nodes as $id => $node) {
            $code .= sprintf("  node_%s [label=\"%s\\n%s\\n\", shape=%s%s];\n",
                $this->dotize($id),
                $id,
                $node['class'],
                $this->options['node']['shape'],
                $this->addAttributes($node['attributes'])
            );
        }

        return $code;
    }

    /**
     * Returns all edges.
     *
     * @return string A string representation of all edges
     */
    private function addEdges()
    {
        $code = '';
        foreach ($this->edges as $id => $edges) {
            foreach ($edges as $edge) {
                $code .= sprintf("  node_%s -> node_%s [label=\"%s\" style=\"%s\"];\n",
                    $this->dotize($id),
                    $this->dotize($edge['to']),
                    $edge['name'],
                    $edge['required'] ? 'filled' : 'dashed'
                );
            }
        }

        return $code;
    }

    /**
     * Finds all edges
     *
     * @return array An array of edges
     */
    private function findEdges()
    {
        $edges = array();

        foreach ($this->workflow->getArcs() as $arc) {
            $edges[$arc->getName()] = array(

            );
        }

        return $edges;
    }

    /**
     * Finds all nodes.
     *
     * @return array An array of all nodes
     */
    private function findNodes()
    {
        $nodes = array();

        foreach ($this->workflow->getNodes() as $node) {
            $condition = $node->getName() == get_class($node) ||
                         $node->getName() == 'workflow.start' ||
                         $node->getName() == 'workflow.finish';
            $nodes[$node->getName()] = array(
                'attributes' => array(
                    'shape' => 'ellipse'
                ),
                'class' => $condition ? '' : $node->getName()
            );
        }

        return $nodes;
    }

    /**
     * Returns the start dot.
     *
     * @return string The string representation of a start dot
     */
    private function startDot()
    {
        return sprintf("digraph sc {\n  %s\n  node [%s];\n  edge [%s];\n\n",
            $this->addOptions($this->options['graph']),
            $this->addOptions($this->options['node']),
            $this->addOptions($this->options['edge'])
        );
    }

    /**
     * Returns the end dot.
     *
     * @return string
     */
    private function endDot()
    {
        return "}\n";
    }

    /**
     * Adds attributes
     *
     * @param array $attributes An array of attributes
     *
     * @return string A comma separated list of attributes
     */
    private function addAttributes($attributes)
    {
        $code = array();
        foreach ($attributes as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return $code ? ', '.implode(', ', $code) : '';
    }

    /**
     * Adds options
     *
     * @param array $options An array of options
     *
     * @return string A space separated list of options
     */
    private function addOptions($options)
    {
        $code = array();
        foreach ($options as $k => $v) {
            $code[] = sprintf('%s="%s"', $k, $v);
        }

        return implode(' ', $code);
    }

    /**
     * Dotizes an identifier.
     *
     * @param string $id The identifier to dotize
     *
     * @return string A dotized string
     */
    private function dotize($id)
    {
        return strtolower(preg_replace('/[^\w]/i', '_', $id));
    }
}
