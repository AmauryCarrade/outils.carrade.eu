<?php

namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class BKPermissionsController
{
    public function generate_permissions(Application $app, Request $request)
    {
        $raw = trim($request->request->get('raw_permissions'));
        $add_comments = $request->request->has('add_comments');
        $powered_comment = $request->request->has('powered_comment');
        $default_grant = self::safe_grant($request->request->get('default_grant'), 'op');
        $parent_permissions_grant = self::safe_grant($request->request->get('parent_permissions_grant'), 'op');
        $default_children_inheritance = self::safe_grant($request->request->get('default_children_inheritance'), 'true');
        $parents_description = trim($request->request->get('parents_description'));

        $generated_yaml = '';
        $generated_java = '';

        // Shared maybe?
        if (!$raw)
        {
            $raw = ($request->query->get("data"));
            if ($raw)
            {
                $compressed = base64_decode($raw);
                if ($compressed !== false)
                {
                    $raw = gzinflate($compressed);
                    if ($raw === false) $raw = '';

                    // Options extraction
                    if ($raw)
                    {
                        $options = $request->query->get('o');
                        if ($options)
                        {
                            $options = gzinflate(base64_decode($options));

                            if ($options)
                            {
                                $options = explode(',', $options);
                                $sizeofopt = sizeof($options);

                                if ($sizeofopt > 0)
                                {
                                    $add_comments = $options[0] == '1';

                                    if ($sizeofopt > 1)
                                    {
                                        $powered_comment = $options[1] == '1';

                                        if ($sizeofopt > 2)
                                        {
                                            $default_grant = self::safe_grant($options[2]);

                                            if ($sizeofopt > 3)
                                            {
                                                $parent_permissions_grant = self::safe_grant($options[3]);

                                                if ($sizeofopt > 4)
                                                {
                                                    $default_children_inheritance = self::safe_grant($options[4]);

                                                    if ($sizeofopt > 5)
                                                    {
                                                        $parents_description = base64_decode($options[5]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                else $raw = '';
            }
        }

        if ($raw)
        {
            $raw_permissions = explode("\n", $raw);

            $permissions = array();

            // Permissions parsing
            foreach ($raw_permissions as $raw_permission)
            {
                if (!trim($raw_permission))
                    continue;

                $exploded_perm = explode(":", $raw_permission);
                $size = sizeof($exploded_perm);

                $name = "";
                $default = $default_grant;
                $description = "";

                if ($size == 0)
                    continue;

                else
                {
                    $name = trim($exploded_perm[0]);

                    if ($size == 2)
                    {
                        $description = trim($exploded_perm[1]);
                    }
                    else if ($size >= 3)
                    {
                        $default = self::safe_grant(trim($exploded_perm[1]), $default_grant);
                        $description = trim($exploded_perm[2]);
                    }
                }

                if (!$name)
                    continue;

                $permissions[] = array(
                    'name' => $name,
                    'description' => $description,
                    'default' => $default
                );
            }


            $generated_yaml = self::generate_permissions_yaml($app, $permissions, $add_comments, $powered_comment, $default_grant, $parent_permissions_grant, $default_children_inheritance, $parents_description);

            $generated_java = self::generate_permissions_enum($app, $permissions);
        }


        $options = ($add_comments ? '1' : '0')
            . ',' . ($powered_comment ? '1' : '0')
            . ',' . $default_grant
            . ',' . $parent_permissions_grant
            . ',' . $default_children_inheritance
            . ',' . base64_encode($parents_description);


        return $app['twig']->render('tools/bukkit/permissions.html.twig', array(
            'raw'           => $raw,

            'generated'       => $generated_yaml != '',
            'generated_yaml'  => $generated_yaml,
            'generated_java'  => $generated_java,

            'raw_base64'    => urlencode(base64_encode(gzdeflate($raw))),
            'options'       => urlencode(base64_encode(gzdeflate($options))),

            'add_comments'                  => $add_comments,
            'powered_comment'               => $powered_comment,
            'default_grant'                 => $default_grant,
            'parent_permissions_grant'      => $parent_permissions_grant,
            'default_children_inheritance'  => $default_children_inheritance,
            'parents_description'           => $parents_description
        ));
    }



    /* **  ***   Plugin.yml generation   ***  ** */

    private static function generatePermissionSection($name, $description = "", $default = "op", $children = array(), $children_default = "true")
    {
        $section = "\t" . $name . ":\n";

        if ($description)
            $section .= "\t\tdescription: \"" . addslashes($description) . "\"\n";

        $section .= "\t\tdefault: " . $default . "\n";

        if (sizeof($children) > 0)
        {
            $section .= "\t\tchildren:\n";
            foreach ($children as $child)
                $section .= "\t\t\t" . $child . ": " . $children_default . "\n";
        }

        return $section . "\n";
    }

    private static function generateInheritedPermissions($leaf, $subtree, $default = "op", $children_default = "true", $parents_description = "")
    {
        $generated = '';

        // Only generated if the subtree is non empty, because else, we're at an
        // explicitely entered permission and they are added separately after.
        if (sizeof ($subtree) > 0)
        {
            if ($leaf != null)
            {
                $children = array();
                foreach ($subtree as $subleaf => $subsubtree)
                {
                    $children[] = $leaf . '.' . $subleaf;
                }

                $generated .= self::generatePermissionSection($leaf . ".*", $parents_description, $default, $children, $children_default);
            }

            foreach ($subtree as $subleaf => $subsubtree)
            {
                $generated .= self::generateInheritedPermissions(($leaf != null ? $leaf . '.' : '') . $subleaf, $subsubtree, $default, $children_default, $parents_description);
            }
        }

        return $generated;
    }

    private static function safe_grant($raw_grant, $default = "op")
    {
        $raw_grant = strtolower($raw_grant);
        return in_array($raw_grant, array("true", "false", "op", "non op")) ? $raw_grant : $default;
    }


    private static function generate_permissions_yaml($app, $permissions, $add_comments, $powered_comment, $default_grant, $parent_permissions_grant, $default_children_inheritance, $parents_description)
    {
        // Building a permissions tree
        $permissions_tree = array();
        foreach ($permissions as $permission)
        {
            $permission_path = explode('.', $permission['name']);

            $leaf = &$permissions_tree;
            foreach ($permission_path as $path_part)
            {
                if (!isset($leaf[$path_part]))
                    $leaf[$path_part] = array();

                $leaf = &$leaf[$path_part];
            }
        }


        $generated = '';


        // Powered-by
        if ($powered_comment)
            $generated .= "# Permissions section generated using " . $app['url_generator']->generate('tools.bukkit.permissions', array(), 1) . "\n";

        // Base key
        $generated .= "permissions:\n";

        // The inherited permissions
        if ($add_comments)
            $generated .= "\n\t# Permissions inheritance\n\n";

        $generated .= self::generateInheritedPermissions(null, $permissions_tree, $parent_permissions_grant, $default_children_inheritance, $parents_description);
        $generated .= "\n";


        // The entered permissions
        if ($add_comments)
            $generated .= "\n\t# Basic permissions\n\n";

        foreach ($permissions as $permission)
        {
            $generated .= self::generatePermissionSection($permission['name'], $permission['description'], $permission['default']);
        }


        // We use tabulations while generating for commodity, but the YML format requires spaces.
        return str_replace("\t", '    ', $generated);
    }



    /* **  ***   Java enum generation   ***  ** */

    private static function generate_permissions_enum($app, $permissions, $package = '', $enumVisibility = 'public', $enumName = 'Permissions', $methodGetPermissionName = 'getPermission', $methodIsGrantedName = 'isGrantedTo', $skip_first_permission_part = true, $add_javadoc = false)
    {
        $generated = '';

        if ($package)
            $generated .= 'package ' . $package . ';' . "\n\n";

        $generated .= 'import org.bukkit.permissions.Permissible;' . "\n\n\n";

        $generated .= $enumVisibility . ' enum ' . $enumName . "\n" . '{' . "\n";


        // ------- Enum constants

        foreach ($permissions as $permission)
        {
            // First, the JavaDoc
            if ($add_javadoc)
            {
                $generated .= <<<JAVADOC_END
	/**
	 * {$permission['description']}
	 */

JAVADOC_END;
            }

            // We first generate a enum constant name based on the permission
            $constantName = strtoupper(str_replace('.', '_', $permission['name']));
            if ($skip_first_permission_part)
            {
                $first_separator_pos = strpos($constantName, '_');
                if ($first_separator_pos !== false)
                {
                    $constantName = substr($constantName, $first_separator_pos + 1);
                }
            }

            $generated .= "\t" . $constantName . '("' . $permission['name'] . '"),' . "\n";

            // With JavaDoc we add a blank line between the constants (lisibility).
            if ($add_javadoc) $generated .= "\n";
        }

        if (!$add_javadoc) $generated .= "\n";
        $generated .= "\t;\n";


        // ------- Enum methods

        $generated .= <<<ENUM_METHODS


	private final String permission;

	$enumName(String permission)
	{
		this.permission = permission;
	}


	/**
	 * @return the permission's name.
	 */
	public String $methodGetPermissionName()
	{
		return permission;
	}

	/**
	 * Checks if this permission is granted to the given permissible.
	 *
	 * @param permissible The permissible to check.
	 * @return {@code true} if this permission is granted to the permissible.
	 */
	public boolean $methodIsGrantedName(Permissible permissible)
	{
		return permissible.hasPermission(permission);
	}

ENUM_METHODS;

        $generated .= '}' . "\n";

        return str_replace("\t", '    ', $generated);
    }
}
