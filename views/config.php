<style>
    .hyd_list_video{
        font-family:Arial, Helvetica, sans-serif;
	color:#666;
	font-size:12px;
	text-shadow: 1px 1px 0px #fff;
	background:#eaebec;
	margin:20px;
	border:#ccc 1px solid;
	border-collapse:separate;
 
	-moz-border-radius:3px;
	-webkit-border-radius:3px;
	border-radius:3px;
 
	-moz-box-shadow: 0 1px 2px #d1d1d1;
	-webkit-box-shadow: 0 1px 2px #d1d1d1;
	box-shadow: 0 1px 2px #d1d1d1;
    } 
    .hyd_list_video tr th {
	padding:18px;
    }
    .hyd_list_video tr td:first-child{
	text-align: left;
	padding-left:20px;
	border-left: 0;
    }
    .hyd_list_video tr td {
	padding:18px;
	border-top: 1px solid #ffffff;
	border-bottom:1px solid #e0e0e0;
	border-left: 1px solid #e0e0e0;
	
	background: #fafafa;
	background: -webkit-gradient(linear, left top, left bottom, from(#fbfbfb), to(#fafafa));
	background: -moz-linear-gradient(top,  #fbfbfb,  #fafafa);
    }
    .hyd_list_video tr:nth-child(even) td{
	background: #f6f6f6;
	background: -webkit-gradient(linear, left top, left bottom, from(#f8f8f8), to(#f6f6f6));
	background: -moz-linear-gradient(top,  #f8f8f8,  #f6f6f6);
    }
    .hyd_list_video tr:last-child td{
	border-bottom:0;
    }
    .hydravid_pugin_version{
    	border-radius: 50%;
    	color: #FFF;
	    background-color: rgb(0, 171, 153);
	    float: right;
	    font-weight: 700;
	    font-size: 20px;
	    width: 80px;
	    height: 80px;
		line-height: 80px;
		text-align:center;
    }

h1, h2 {
    font-size: 28px;
    color: #45ada8;
}
h3{
    font-size: 20px;
    color: #45ada8;
}
.sign-in input[type="text"], .sign-in input[type="password"] {
    float: left;
    width: 100%;
    display: block;
    border: 1px solid #45ada7;
    background-color: transparent;
    font-size: 16px;
    padding: 8px 14px;
    color: #547980;
}
.sign-in label {
    font-size: 16px;
    text-align: left;
    color: #547980;
	display: block;
}
.sign-in input[type="submit"] {
    background-color: #45ada7;
    color: #FFF;
    font-size: 14px;
    border-radius: 0;
    border: none;
	width: 150px;
}
.btn {
    display: inline-block;
    padding: 6px 12px;
    margin-bottom: 0;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
}
table{
	width: 600px;
}
@media screen and (max-width: 768px) {
    .sign-in table{
		width: 100%;
	}
}
input[type=checkbox]:checked:before {
    color: rgb(0, 171, 153);
}
#message.update-nag{
	display: block;
}
</style>

<div class="wrap">
<h2><?=esc_html( "Hydravid" )?></h2>
<h3><?=__('Settings','example_plugin'); ?></h3>
<div class="hydravid_pugin_version"><?=esc_html(HYDRAVI__VERSION)?></div>
<?php if(!$hydravid_site){ ?>
    <h1>Registration site on Hydravid</h1>
    <form method="post" class="sign-in" action="<?=$_SERVER['REQUEST_URI'];?>">
		<table>
			<tr>
				<td><label>Username:</label></td>
				<td><input type="text" name="login" /></td>
			</tr>
			<tr>
				<td><label>Password:</label></td>
				<td><input type="text" name="pass" /></td>
			</tr>
			<tr>
				<td><input type="hidden" name="cmd" value="register_hydravid"></td>
				<td><input type="submit" name="submit" value="Log In" class="btn" /></td>
			</tr>
		</table>
    </form>
<?php  }else{   ?>
    <h2><?=esc_html( $username );?></h2>
	
	<script type="text/javascript" src="http://code.jquery.com/jquery-2.1.3.js"></script>
	<link href="http://wenzhixin.net.cn/p/multiple-select/multiple-select.css" rel="stylesheet"/>

	<?php if(!empty($categories)){ ?>
		<form method="post" class="sign-in" action="<?=$_SERVER['REQUEST_URI'];?>">
			<label>Select categories:</label>
			<select multiple="multiple" style="width: 300px;" name="categories[]">
				<?php foreach($categories as $key => $category) { ?>
				<option <?=in_array($category, $active_categories) ? 'selected' : ''; ?>><?=esc_html( $category )?></option>
				<?php } ?>
			</select>
			<input type="hidden" name="cmd" value="selected_category">
			<input type="submit" name="submit" class="btn" />
		</form>
	<?php } ?>
    <script src="http://wenzhixin.net.cn/p/multiple-select/multiple-select.js"></script>
    <script>
        $('select').multipleSelect();
    </script>
	
<?php   }   ?>
    
</div>