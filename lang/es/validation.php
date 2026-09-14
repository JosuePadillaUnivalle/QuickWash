<?php
return [
    'required' => 'El campo :attribute es obligatorio.',
    'email' => 'El campo :attribute debe ser un correo válido.',
    'unique' => 'Este :attribute ya está registrado.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'string' => 'El campo :attribute debe ser texto.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'min' => ['string' => 'El campo :attribute debe tener al menos :min caracteres.', 'numeric' => 'El campo :attribute debe ser al menos :min.'],
    'max' => ['string' => 'El campo :attribute no debe superar :max caracteres.', 'numeric' => 'El campo :attribute no debe superar :max.'],
    'date_format' => 'El campo :attribute no tiene un formato válido.',
    'after_or_equal' => 'La :attribute no puede ser anterior a :date.',
    'before_or_equal' => 'La :attribute no puede ser posterior a :date.',
    'exists' => 'El valor seleccionado para :attribute no existe.',
    'in' => 'El valor seleccionado para :attribute no es válido.',
    'attributes' => ['name' => 'nombre', 'email' => 'correo electrónico', 'password' => 'contraseña', 'date' => 'fecha', 'time' => 'hora', 'machine_id' => 'máquina', 'capacity' => 'capacidad', 'type' => 'tipo', 'location' => 'ubicación', 'status' => 'estado', 'search' => 'búsqueda'],
];
