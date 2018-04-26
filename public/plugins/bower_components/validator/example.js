$(document).ready(function() {
	$('#loginForm').bootstrapValidator({
		fields: {
			admin_email: {
				validators: {
					notEmpty: {
						message: 'Email address is required and can\'t be empty'
					},
					emailAddress: {
						message: 'Please enter valid email address.'
					}
				}
			},
			admin_password: {
				validators: {
					notEmpty: {
						message: 'Password is required and can\'t be empty'
					},
				}
			}
		}
	});

	$('#formForgotPassword').bootstrapValidator({
		fields: {
			txtemail: {
				validators: {
					notEmpty: {
						message: 'Email address is required and can\'t be empty'
					},
					emailAddress: {
						message: 'Please enter valid email address.'
					}
				}
			}
		}
	});

	$('#formAdmin').bootstrapValidator({
		excluded: ':disabled',
		fields: {
			editProfilePic: {
				validators: {
					notEmpty: {
						message: 'Please select file.'
					},
					file: {
						extension: 'jpeg,jpg,png',
						message: 'The selected file is not valid.'
					}
				}
			}
		}
	});

	$('#formAccountSetting').bootstrapValidator({
		fields: {
			currentPassword: {
				validators: {
					notEmpty: {
						message: 'Current password is required and can\'t be empty'
					},
					/*stringLength: {
						min: 6,
						message: 'Current password should be of 6 digits.'
					},*/
				}
			},
			newPassword: {
				validators: {
					notEmpty: {
						message: 'New password is required and can\'t be empty'
					},
					stringLength: {
						min: 6,
						message: 'New password should be of 6 digits.'
					},
				}
			},
			retypePassword: {
				validators: {
					notEmpty: {
						message: 'Retype password is required and can\'t be empty'
					},
					stringLength: {
						min: 6,
						message: 'Retype password should be of 6 digits.'
					},
				}
			},
		}
	});

	$('#formAddClientCompany').bootstrapValidator({
		excluded: ':disabled',
		fields: {
			companyName: {
				validators: {
					notEmpty: {
						message: 'Company name is required and can\'t be empty.'
					},
					regexp: {
						regexp: /^[a-z\s]+$/i,
						message: 'Company name can only consist of alphabetical.'
					}
				}
			},
			companyEmail: {
				validators: {
					notEmpty: {
						message: 'Email address is required and can\'t be empty'
					},
					emailAddress: {
						message: 'Please enter valid email address.'
					}
				}
			},
			companyPhoneNo: {
				validators: {
					notEmpty: {
						message: 'Phone number is required and can\'t be empty'
					},
					stringLength: {
						min: 16,
						max: 16,
						message: 'Phone number should be of 10 digits.'
					}
				}
			},
			locationAddress:{
				validators:{
					notEmpty: {
						message:'Address is required and can\'t be empty.'
					}
				}
			},
			subAddress:{
				validators:{
					stringLength: {
						min: 0,
					}
				}
			},
			city:{
				validators:{
					stringLength: {
						min: 0,
					}
				}
			},
			state:{
				validators:{
					stringLength: {
						min: 0,
					}
				}
			},
			zipcode:{
				validators:{
					stringLength: {
						min: 0,
					}
				}
			}
		}
	});

	$('#companyPhoneNo').on('keyup', function() {
		$('#formAddClientCompany').bootstrapValidator('revalidateField', 'companyPhoneNo');
	});

	$('#formAddEmployee').bootstrapValidator({
		excluded: ':disabled',
		fields: {			
			employeeFirstName: {
				validators: {
					notEmpty: {
						message: 'Employee first name is required and can\'t be empty.'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9\s]+$/i,
						message: 'Employee first name can only consist of alphanumeric.'
					}
				}
			},
			employeeLastName: {
				validators: {
					notEmpty: {
						message: 'Employee last name is required and can\'t be empty.'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9\s]+$/i,
						message: 'Employee last name can only consist of alphanumeric.'
					}
				}
			},
			employeePhoneNo: {
				validators: {
					notEmpty: {
						message: 'Phone number is required and can\'t be empty'
					},
					stringLength: {
						min: 16,
						max: 16,
						message: 'Phone number should be of 10 digits.'
					}
				}
			},
			employeeEmail: {
				validators: {
					notEmpty: {
						message: 'Email address is required and can\'t be empty'
					},
					emailAddress: {
						message: 'Please enter valid email address.'
					}
				}
			},
			employeeType:{
				validators:{
					notEmpty: {
						message:'Employee type is required and can\'t be empty.'
					}
				}
			},
		}
	});

	$('#employeePhoneNo').on('keyup', function() {
		$('#formAddEmployee').bootstrapValidator('revalidateField', 'employeePhoneNo');
	});

	$('#formAddAdmin').bootstrapValidator({
		excluded: ':disabled',
		fields: {			
			adminFirstName: {
				validators: {
					notEmpty: {
						message: 'Admin first name is required and can\'t be empty.'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9\s]+$/i,
						message: 'Admin first name can only consist of alphanumeric.'
					}
				}
			},
			adminLastName: {
				validators: {
					notEmpty: {
						message: 'Admin last name is required and can\'t be empty.'
					},
					regexp: {
						regexp: /^[a-zA-Z0-9\s]+$/i,
						message: 'Admin last name can only consist of alphanumeric.'
					}
				}
			},
			adminPhoneNo: {
				validators: {
					notEmpty: {
						message: 'Phone number is required and can\'t be empty'
					},
					stringLength: {
						min: 16,
						max: 16,
						message: 'Phone number should be of 10 digits.'
					}
				}
			},
			adminEmail: {
				validators: {
					notEmpty: {
						message: 'Email address is required and can\'t be empty'
					},
					emailAddress: {
						message: 'Please enter valid email address.'
					}
				}
			},
		}
	});

	$('#adminPhoneNo').on('keyup', function() {
		$('#formAddAdmin').bootstrapValidator('revalidateField', 'adminPhoneNo');
	});

	$('#formImportProspect').bootstrapValidator({
		excluded: ':disabled',
		fields: {
			importProspect: {
				validators: {
					notEmpty: {
						message: 'Please select file.'
					},
					file: {
						extension: 'csv',
						message: 'The selected file is not valid.'
					}
				}
			}
		}
	});

	$('#formImportSubscriber').bootstrapValidator({
		excluded: ':disabled',
		fields: {
			importSubscriber: {
				validators: {
					notEmpty: {
						message: 'Please select file.'
					},
					file: {
						extension: 'csv',
						message: 'The selected file is not valid.'
					}
				}
			}
		}
	});

	$('#formImportAgreement').bootstrapValidator({
		excluded: ':disabled',
		fields: {
			importAgreement: {
				validators: {
					notEmpty: {
						message: 'Please select file.'
					},
					file: {
						extension: 'pdf',
						message: 'The selected file is not valid.'
					}
				}
			}
		}
	});

});