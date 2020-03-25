<?php



class HTMLPurifier_Strategy_FixNesting extends HTMLPurifier_Strategy
{

    
    public function execute($tokens, $config, $context)
    {

                
                        $top_node = HTMLPurifier_Arborize::arborize($tokens, $config, $context);

                $definition = $config->getHTMLDefinition();

        $excludes_enabled = !$config->get('Core.DisableExcludes');

                                        $is_inline = $definition->info_parent_def->descendants_are_inline;
        $context->register('IsInline', $is_inline);

                $e =& $context->get('ErrorCollector', true);

                
                                        $exclude_stack = array($definition->info_parent_def->excludes);

                        $node = $top_node;
                list($token, $d) = $node->toTokenPair();
        $context->register('CurrentNode', $node);
        $context->register('CurrentToken', $token);

                
                                                                                                                                
        $parent_def = $definition->info_parent_def;
        $stack = array(
            array($top_node,
                  $parent_def->descendants_are_inline,
                  $parent_def->excludes,                   0)
            );

        while (!empty($stack)) {
            list($node, $is_inline, $excludes, $ix) = array_pop($stack);
                        $go = false;
            $def = empty($stack) ? $definition->info_parent_def : $definition->info[$node->name];
            while (isset($node->children[$ix])) {
                $child = $node->children[$ix++];
                if ($child instanceof HTMLPurifier_Node_Element) {
                    $go = true;
                    $stack[] = array($node, $is_inline, $excludes, $ix);
                    $stack[] = array($child,
                                                                        $is_inline || $def->descendants_are_inline,
                        empty($def->excludes) ? $excludes
                                              : array_merge($excludes, $def->excludes),
                        0);
                    break;
                }
            };
            if ($go) continue;
            list($token, $d) = $node->toTokenPair();
                        if ($excludes_enabled && isset($excludes[$node->name])) {
                $node->dead = true;
                if ($e) $e->send(E_ERROR, 'Strategy_FixNesting: Node excluded');
            } else {
                                                                $children = array();
                foreach ($node->children as $child) {
                    if (!$child->dead) $children[] = $child;
                }
                $result = $def->child->validateChildren($children, $config, $context);
                if ($result === true) {
                                        $node->children = $children;
                } elseif ($result === false) {
                    $node->dead = true;
                    if ($e) $e->send(E_ERROR, 'Strategy_FixNesting: Node removed');
                } else {
                    $node->children = $result;
                    if ($e) {
                                                if (empty($result) && !empty($children)) {
                            $e->send(E_ERROR, 'Strategy_FixNesting: Node contents removed');
                        } else if ($result != $children) {
                            $e->send(E_WARNING, 'Strategy_FixNesting: Node reorganized');
                        }
                    }
                }
            }
        }

                
                $context->destroy('IsInline');
        $context->destroy('CurrentNode');
        $context->destroy('CurrentToken');

                
        return HTMLPurifier_Arborize::flatten($node, $config, $context);
    }
}

