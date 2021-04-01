<?php

function find_all_subjects($options=[]) {
    global $db;

    $visible = $options['visible'] ?? false;

    $sql = "SELECT * FROM subjects ";
    if($visible) {
        $sql .= "WHERE visible = true ";
    }
    $sql .= "ORDER BY position ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function find_subject_by_id($id, $options=[]) {
    global $db;
    
    $visible = $options['visible'] ?? false;

    $sql = "SELECT * FROM subjects ";
    $sql .= "WHERE id = '" . db_escape($db, $id) . "' ";
    if($visible) {
        $sql .= "AND visible=true ";
    }
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);

    $subject = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $subject; // returns an assoc array
}

function shift_subject_positions($start_pos, $end_pos, $current_id=0) {
    global $db;

    if($start_pos == $end_pos) { return; }

    $sql = "UPDATE subjects ";
    if($start_pos == 0) {
        // new item, +1 to items >= $end_pos
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE position >= " . db_escape($db, $end_pos) . " ";
    } elseif($end_pos == 0) {
        // delete item, -1 from items > $start_pos
        $sql .= "SET position = position - 1 ";
        $sql .= "WHERE position > " . db_escape($db, $start_pos) . " ";
    } elseif($start_pos < $end_pos) {
        // move later, -1 from items between (including $end_pos)
        $sql .= "SET position = position - 1 ";
        $sql .= "WHERE position > " . $start_pos . " ";
        $sql .= "AND position <= " . $end_pos . " ";
    } elseif($start_pos > $end_pos) {
        // move earlier, +1 to items between (including $end_pos
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE position < " . $start_pos . " ";
        $sql .= "AND position >= " . $end_pos . " ";
    }
    // Exclude the current_id in the SQL WHERE clause
    $sql .= "AND id != '" . db_escape($db, $current_id) . "'";

    $result = mysqli_query($db, $sql); // UPDATE returns true/false
    
    if($result) {
        return true;
    } else {
        // UPDATE fails
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function validate_subject($subject) {
    $errors = [];
    
    // menu_name
    if(is_blank($subject['menu_name'])) {
        $errors[] = "Name cannot be blank.";
    } elseif(!has_length($subject['menu_name'], ['min' => 2, 'max' => 255])) {
        $errors[] = "Name must be between 2 and 255 characters.";
    }

    // position
    // make sure we are working with an integer
    $position_int = (int) $subject['position'];
    if($position_int <= 0) {
        $errors[] = "Position must be greater than zero.";
    }
    if($position_int > 999) {
        $errors[] = "Position must be less than 999.";
    }

    // visible
    // make sure we are working with a string
    $visible_str = (string) $subject['visible'];
    if(!has_inclusion_of($visible_str, ["0", "1"])) {
        $errors[] = "Visible must be true or false.";
    }

    return $errors;
}

function insert_subject($subject) {
    global $db;
    
    $errors = validate_subject($subject);
    if(!empty($errors)) {
        return $errors;
    }

    shift_subject_positions(0, $subject['position']);

    $sql = "INSERT INTO subjects ";
    $sql .= "(menu_name, position, visible) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $subject['menu_name']) . "',";
    $sql .= "'" . db_escape($db, $subject['position']) . "',";
    $sql .= "'" . db_escape($db, $subject['visible']) . "'";
    $sql .= ")";
    $result = mysqli_query($db, $sql);
    // For INSERT statements, $result is true/false
    if($result) {
        return true;
    } else {
        // INSERT failed
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function update_subject($subject) {
    global $db;

    $errors = validate_subject($subject);
    if(!empty($errors)) {
        return $errors;
    }

    $subject_0 = find_subject_by_id($subject['id']);
    $start_pos = $subject_0['position'];
    mysqli_free_result($subject_0);
    $end_pos = $subject['position'];
    shift_subject_positions($start_pos, $end_pos, $subject['id']);

    $sql = "UPDATE subjects SET ";
    $sql .= "menu_name = '" . db_escape($db, $subject['menu_name']) . "', ";
    $sql .= "position = '" . db_escape($db, $subject['position']) . "', ";
    $sql .= "visible = '" . db_escape($db, $subject['visible']) . "' ";
    $sql .= "WHERE id = '" . db_escape($db, $subject['id']) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    // For UPDATE statements, $result is true/false
    if($result) {
        return true;
    } else {
        // UPDATE failed
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function delete_subject($id) {
    global $db;
    
    $subject = find_subject_by_id($id);
    $start_pos = $subject['position'];
    mysqli_free_result($subject);
    
    shift_subject_positions($start_pos, 0, $id);

    $sql = "DELETE FROM subjects ";
    $sql .= "WHERE id = '" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);

    // For DELETE statements, $result is true/false
    if ($result) {
        return true;
    } else {
        // DELETE failed
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function find_all_pages($options=[]) {
    global $db;

    $visible = $options['visible'] ?? false;

    $sql = "SELECT * FROM pages ";
    if($visible) {
        $sql .= "WHERE visible = true ";
    }
    $sql .= "ORDER BY subject_id ASC, position ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function find_page_by_id($id, $options=[]) {
    global $db;
    
    $visible = $options['visible'] ?? false;

    $sql = "SELECT * FROM pages ";
    $sql .= "WHERE id = '" . db_escape($db, $id) . "'";
    if($visible) {
        $sql .= "AND visible = true ";
    }
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);

    $page = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $page;
}

function shift_page_positions($start_pos, $end_pos, $current_id=0) {
    global $db;

    $page = find_page_by_id($current_id);
    $subject_id = $_POST['subject_id'] ?? $page['subject_id'];
    mysqli_free_result($page);

    $sql = "UPDATE pages ";
    if($start_pos == 0) {
        // new item, +1 to items >= $end_pos
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE position >= " . db_escape($db, $end_pos) . " ";
    } elseif($end_pos == 0) {
        // delete item, -1 from items > $start_pos
        $sql .= "SET position = position - 1 ";
        $sql .= "WHERE position > " . db_escape($db, $start_pos) . " ";
    } elseif($start_pos < $end_pos) {
        // move later, -1 from items between (including $end_pos)
        $sql .= "SET position = position - 1 ";
        $sql .= "WHERE position > " . $start_pos . " ";
        $sql .= "AND position <= " . $end_pos . " ";
    } elseif($start_pos > $end_pos) {
        // move earlier, +1 to items between (including $end_pos
        $sql .= "SET position = position + 1 ";
        $sql .= "WHERE position < " . $start_pos . " ";
        $sql .= "AND position >= " . $end_pos . " ";
    }
    // Exclude the current_id in the SQL WHERE clause
    $sql .= "AND id != '" . db_escape($db, $current_id) . "' ";
    $sql .= "AND subject_id = '" . db_escape($db, $subject_id) . "'";

    $result = mysqli_query($db, $sql); // UPDATE returns true/false
    
    if($result) {
        return true;
    } else {
        // UPDATE fails
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function validate_page($page) {
    $errors = [];

    // menu_name
    if(is_blank($page['menu_name'])) {
        $errors[] = "Name cannot be blank.";
    } elseif(!has_length($page['menu_name'], ['min' => 2, 'max' => 255])) {
        $errors[] = "Name must be between 2 and 255 characters.";
    }
    $current_id = $page['id'] ?? '0';
    if(!has_unique_page_menu_name($page['menu_name'], $current_id)) {
        $errors[] = "Name must be a unique value.";
    }

    // subject_id
    if(is_blank($page['subject_id'])) {
        $errors[] = "Subject cannot be blank.";
    }

    // position
    $position_int = (int) $page['position'];
    if($position_int <= 0) {
        $errors[] = "Position must be greater than zero.";
    }
    if($position_int > 999) {
        $errors[] = "Position must be less than 999.";
    }

    // visible
    $visible_str = (string) $page['visible'];
    if(!has_inclusion_of($visible_str, ["0", "1"])) {
        $errors[] = "Visible must be true or false.";
    }

    // content
    if (is_blank($page['content'])) {
        $errors[] = "Content cannot be blank.";
    }

    return $errors;
}

function insert_page($page) {
    global $db; 

    $errors = validate_page($page);
    if(!empty($errors)) {
        return $errors;
    }

    shift_page_positions(0, $page['position']);

    $sql = "INSERT INTO pages ";
    $sql .= "(subject_id, menu_name, position, visible, content) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $page['subject_id']) . "', ";
    $sql .= "'" . db_escape($db, $page['menu_name']) . "', ";
    $sql .= "'" . db_escape($db, $page['position']) . "', ";
    $sql .= "'" . db_escape($db, $page['visible']) . "', ";
    $sql .= "'" . db_escape($db, $page['content']) . "'";
    $sql .= ')';

    $result = mysqli_query($db, $sql);

    if ($result) {
        return true;
    } else {
        // INSERT failed
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function update_page($page) {
    global $db; 
    
    $errors = validate_page($page);
    if(!empty($errors)) {
        return $errors;
    }

    $page_0 = find_page_by_id($page['id']);
    $start_pos = $page_0['position'];
    mysqli_free_result($page_0);
    $end_pos = $page['position'];
    shift_page_positions($start_pos, $end_pos, $page['id']);

    $sql = "UPDATE pages SET ";
    $sql .= "subject_id = '" . db_escape($db, $page['subject_id']) . "', ";
    $sql .= "menu_name = '" . db_escape($db, $page['menu_name']) . "', ";
    $sql .= "position = '" . db_escape($db, $page['position']) . "', ";
    $sql .= "visible = '" . db_escape($db, $page['visible']) . "', ";
    $sql .= "content = '" . db_escape($db, $page['content']) . "' ";
    $sql .= "WHERE id = '" . db_escape($db, $page['id']) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    if ($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function delete_page($id) {
    global $db;
    
    $page = find_page_by_id($id);
    $start_pos = $page['position'];
    mysqli_free_result($page);

    shift_page_positions($start_pos, 0, $id);

    $sql = "DELETE FROM pages ";
    $sql .= "WHERE id = '" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";
    
    $result = mysqli_query($db, $sql);

    if ($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

function find_pages_by_subject_id($subject_id, $options=[]) {
    global $db;
    
    $visible = $options['visible'] ?? false;

    $sql = "SELECT * FROM pages ";
    $sql .= "WHERE subject_id = '" . db_escape($db, $subject_id) . "' ";
    if($visible) {
        $sql .= "AND visible = true ";
    }
    $sql .= "ORDER BY position ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function count_pages_by_subject_id($subject_id, $options=[]) {
    global $db;
    
    $visible = $options['visible'] ?? false;

    $sql = "SELECT COUNT(id) FROM pages ";
    $sql .= "WHERE subject_id = '" . db_escape($db, $subject_id) . "' ";
    if($visible) {
        $sql .= "AND visible = true ";
    }
    $sql .= "ORDER BY position ASC";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    
    $count =$row[0];
    return $count;
}

function find_all_admins() {
    global $db;

    $sql = "SELECT * FROM admins ";
    $result = mysqli_query($db, $sql);
    confirm_result_set($result);
    return $result;
}

function find_admin_by_id($id) {
    global $db;

    $sql = "SELECT * FROM admins ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);

    $admin = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $admin;
}

function find_admin_by_username($username) {
    global $db;

    $sql = "SELECT * FROM admins ";
    $sql .= "WHERE username='" . db_escape($db, $username) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    confirm_result_set($result);

    $admin = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $admin;
}

function validate_admin($admin, $options=[]) {
    $errors = [];

    $password_required = $options['password_required'] ?? true;

    // first_name
    if(is_blank($admin['first_name'])) {
        $errors[] = "First name cannot be blank.";
    } elseif(!has_length($admin['first_name'], ['min' => 2, 'max' => 255])) {
        $errors[] = "First name must be between 2 and 255 characters.";
    }

    // last_name
    if(is_blank($admin['last_name'])) {
        $errors[] = "Last name cannot be blank.";
    } elseif(!has_length($admin['last_name'], ['min' => 2, 'max' => 255])) {
        $errors[] = "Last name must be between 2 and 255 characters.";
    }

    // email
    if(is_blank($admin['email'])) {
        $errors[] = "Email cannot be blank.";
    } elseif(!has_length($admin['email'], ['max' => 255])) {
        $errors[] = "Email must be less than 255 characters.";
    } elseif(!has_valid_email_format($admin['email'])) {
        $errors[] = "Email must be in the valid format.";
    }

    // username
    if(is_blank($admin['username'])) {
        $errors[] = "Username cannot be blank.";
    } elseif(!has_length($admin['username'], ['min' => 8, 'max' => 255])) {
        $errors[] = "Username must be between 8 and 255 characters.";
    }
    $current_id = $admin['id'] ?? '0';
    if(!has_unique_admin_username($admin['username'], $current_id)) {
        $errors[] = "Username has already been taken.";
    }

    // password
    if($password_required) {
        if(is_blank($admin['password'])) {
            $errors[] = "Password cannot be blank.";
        } elseif(!has_length($admin['password'], ['min' => 12])) {
            $errors[] = "Password must be more than 12 characters.";
        }
        if(!preg_match("#[0-9]+#", $admin['password'])) {
            $errors[] = "Password must contain at least one number.";
        }
        if(!preg_match("#[A-Z]+#", $admin['password'])) {
            $errors[] = "Password must contain at least one capital letter.";
        }
        if(!preg_match("#[a-z]+#", $admin['password'])) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }
        if(!preg_match("#[^A-Za-z0-9\s]+#", $admin['password'])) {
            $errors[] = "Password must contain at least one symbol.";
        }

        // confirm_password
        if(is_blank($admin['confirm_password'])) {
            $errors[] = "Confirm password cannot be blank.";
        } elseif($admin['password'] !== $admin['confirm_password']) {
            $errors[] = "Password and confirm password must match.";
        }
    }

    return $errors;
}

function insert_admin($admin) {
    global $db;

    $errors = validate_admin($admin);
    if(!empty($errors)) {
        return $errors;
    }

    $admin['hashed_password'] = password_hash($admin['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins ";
    $sql .= "(first_name, last_name, email, username, hashed_password) ";
    $sql .= "VALUES (";
    $sql .= "'" . db_escape($db, $admin['first_name']) . "', ";
    $sql .= "'" . db_escape($db, $admin['last_name']) . "', ";
    $sql .= "'" . db_escape($db, $admin['email']) . "', ";
    $sql .= "'" . db_escape($db, $admin['username']) . "', ";
    $sql .= "'" . db_escape($db, $admin['hashed_password']) . "'";
    $sql .= ")";

    $result = mysqli_query($db, $sql);
    if($result) {
        return true;
    } else {
        // INSERT failed
        echo mysqli_error($db);
        db_disconnect();
        exit;
    }
}

function update_admin($admin) {
    global $db;

    $password_sent = !is_blank($admin['password']);

    $errors = validate_admin($admin, ['password_required' => $password_sent]);
    if(!empty($errors)) {
        return $errors;
    }

    $admin['hashed_password'] = password_hash($admin['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE admins SET ";
    $sql .= "first_name = '" . db_escape($db, $admin['first_name']) . "', ";
    $sql .= "last_name = '" . db_escape($db, $admin['last_name']) . "', ";
    $sql .= "email = '" . db_escape($db, $admin['email']) . "', ";
    if($password_sent) {
        $sql .= "hashed_password = '" . db_escape($db, $admin['hashed_password']) . "', ";
    }
    $sql .= "username = '" . db_escape($db, $admin['username']) . "' ";
    $sql .= "WHERE id = '" . db_escape($db, $admin['id']) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    if($result) {
        return true;
    } else {
        // EDIT failed
        echo mysqli_error($db);
        db_disconnect();
        exit;
    }

}

function delete_admin($id) {
    global $db;

    $sql = "DELETE FROM admins ";
    $sql .= "WHERE id='" . db_escape($db, $id) . "' ";
    $sql .= "LIMIT 1";

    $result = mysqli_query($db, $sql);
    
    if ($result) {
        return true;
    } else {
        echo mysqli_error($db);
        db_disconnect($db);
        exit;
    }
}

?>