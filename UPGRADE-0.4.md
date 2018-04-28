# UPGRADE FROM 0.3 to 0.4

## UserBundle

- Added support for Symfony Messenger and is favored over SimpleBus by default
- Renamed default Twig variables

    Before:

    ```
    {# Nullable #}
    {{ msgphp_user.user }}
    {{ msgphp_user.userId }}
    ```

    After:

    ```
    {# Not nullable #}
    {{ msgphp_user.current }}
    {{ msgphp_user.id }}
    ```
