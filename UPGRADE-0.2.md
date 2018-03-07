# UPGRADE FROM 0.1 to 0.2

## Eav

- Decoupled entity identifiers using abstract entities

## EavBundle

- Removed `%msgphp.default_data_type%` DI parameter, use `default_id_type` bundle configuration instead
- Removed `data_type_mapping` bundle configuration, use `id_type_mapping` instead

## User

- Decoupled entity identifiers using abstract entities

## UserBundle

- Removed `%msgphp.default_data_type%` DI parameter, use `default_id_type` bundle configuration instead
- Removed `data_type_mapping` bundle configuration, use `id_type_mapping` instead
- Configured param converter by name

    Before:

    ```
    @ParamConverter("argument", options={"current": true})
    ```

    After:

    ```
    @ParamConverter("argument", converter="msgphp.current_user")
    ```
