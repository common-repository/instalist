(function ($) {
	"use strict";
	$(document).ready(function () {
		// if (typeof _ !== 'undefined' && typeof _.camelCase === 'function') {
		//     // Lodash and startCase function are available
		//     var myString = "hello world";
		//     var result =_.camelCase(myString);
		//     console.log(result); // Should output: "Hello World"
		// } else {
		//     console.error('Lodash is not loaded or _.camelCase is not a function');
		// }
		// alert(_.isNumber('3'));
		// alert(_.chunk(['a', 'b', 'c', 'd'], 3));

		
		function togglePluginsOverlay(visible) {
			if (visible) {
				$("#plugins-overlay").addClass("overlay-visible");
			} else {
				$("#plugins-overlay").removeClass("overlay-visible");
			}
		}

		function toggleOverlay(visible) {
			if (visible) {
				$("body").append(
					'<div id="global-overlay" class="page-overlay"><div id="loader-container"><div class="plgsspinner"></div></div></div>'
				);
				$("#global-overlay").addClass("overlay-visible");
			} else {
				$("#global-overlay").removeClass("overlay-visible");
				$("body").remove($("#global-overlay"));
			}
		}

		// #region ADD PLUGIN TO LIST
		$("#add_plugin_button").on("click", function (e) {
			e.preventDefault();
			if ("" === $("#plugin_name").val() || "" === $("#plugin_slug").val()) {
				Swal.fire({
					titleText: "Error",
					text: "Can'r find the slug for this plugin.",
					showDenyButton: false,
					showCancelButton: false,
					confirmButtonText: "Ok",
					icon: "error",
					customClass: {
						actions: "my-actions",
						confirmButton: "sweet-yes",
						denyButton: "sweet-no",
					},
				}).then((result) => {
					return;
				});
			} else {
				addPluginToList();
			}
		});

		function addPluginToList() {
			var nonce = $("#_wpnonce").val();
			let pluginName = $("#plugin_name").val();
			let pluginSlug = $("#plugin_slug").val();
			let pluginIcon = $("#plugin_icon").val();
			let pd = pluginIcon + ";" + pluginName + ";" + pluginSlug;
			$("div#plugins-list-table .table-body").append(
				`<div class="table-row" id="${pluginSlug}">
				<div class="table-column"><input type="checkbox" class="plugin_to_install" name="plugin_to_install" value="${pd}" /></div>
				<div class="table-column"><img src="${pluginIcon}" /></div>
				<div class="table-column">${pluginName}</div>
				<div class="table-column">${pluginSlug}</div>
				<div class="table-column">
				<button class="button remove_plugin_button" data-slug="${pluginSlug}">Remove</button>
				</div>
					<input type="hidden" class="plugin_name" name="plugin_name[]" value="${pluginName}" />
					<input type="hidden" class="plugin_slug" name="plugin_slug[]" value="${pluginSlug}" />
					<input type="hidden" class="plugin_icon" name="plugin_icon[]" value="${pluginIcon}" />
				</div>
				`
			);

			$("#plugin_name").val("");
			$("#plugin_slug").val("");
			$("#plugin_icon").val("");
			$("#plugins_result_list").val("");
		}

		// #endregion ADD PLUGIN TO LIST

		// #region REMOVE PLUGIN FROM LIST
		$(document).on("click", ".remove_plugin_button", function (e) {
			e.preventDefault();
			let slug = $(this).data("slug");
			Swal.fire({
				titleText: "Confirm, please.",
				html:
					"Are you sure you want to remove the plugin <strong>" +
					slug +
					"</strong> from the list?",
				showDenyButton: true,
				showCancelButton: false,
				confirmButtonText: "Yes",
				denyButtonText: "No",
				icon: "question",
			}).then((result) => {
				if (result.isConfirmed) {
					$(".table-row#" + slug).remove();
					markPostAsDirty();
				}
			});
		});

		var isDirty;
		// Funzione per segnare il post come modificato
		function markPostAsDirty() {
			console.log('markPostAsDirty');
			isDirty = true;  // Segna lo stato come "modificato"
			window.onbeforeunload = function() {
				if (isDirty) {
					return "You have unsaved changes. Are you sure you want to leave?";
				}
			};
			// if (window.wp && wp.data) {
			// 	console.log('windows.wp');
			// 	wp.data.dispatch('core/editor').editPost({});  // Segnala una modifica
			// }
		}

		// Quando l'utente salva il post, rimuovi il listener e resetta lo stato.
		$(document).on("submit", "form", function() {
			isDirty = false;  // Resetta lo stato a "non modificato"
			window.onbeforeunload = null;  // Rimuovi il messaggio di conferma
		});

		// #endregion REMOVE PLUGIN FROM LIST

		// #region INSTALL SELECTED PLUGINS

		$("#install_selected_plugins_button").on("click", function (e) {

			if ($(this).hasClass('disabled')) {
				return;
			}
			e.preventDefault();
			var checkedCheckboxes = $('.plugin_to_install:checked');
			if (checkedCheckboxes.length <= 0) {
				// alert();
				// return;
				Swal.fire({
					titleText:
						"No plugin selected",
					text: "Please, select at least one plugin to install and activate.",
					showDenyButton: false,
					showCancelButton: false,
					confirmButtonText: "Ok",
					icon: "warning",
				}).then((result) => {

				});
				return;
			}
			Swal.fire({
				titleText:
					"Please, confirm",
				text: "Do you want to install and activate the selected plugin(s)?",
				showDenyButton: true,
				showCancelButton: false,
				confirmButtonText: "Yes",
				denyButtonText: "No",
				icon: "question",
			}).then((result) => {
				if (result.isConfirmed) {
					installSelectedPlugins();
				}
			});
		});

		function installSelectedPlugins() {
			toggleOverlay(true);
			let nonce = $("#_wpnonce_inst_sel").val();
			let plugin_data = [];
			$.each($(".plugin_to_install"), function () {
				if ($(this).is(":checked")) {
					plugin_data.push($(this).val());
				}
			});
			$.ajax({
				type: "post",
				url: ajaxurl,
				data: {
					nonce: nonce,
					plugin_data: plugin_data,
					action: "inslst_install_selected_plugins",
				},
				success: function (data) {
					toggleOverlay(false);
					if (data.result === "success") {
						Swal.fire({
							titleText: "Congratulations!",
							text: "Your plugins are being installed and activated.",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "success",
						}).then((result) => {
							return false;
						});
					}
				},
				error: function (error) {
					toggleOverlay(false);
					console.log("error");
					console.log(error);
				},
			});
		}
		// #endregion INSTALL SELECTED PLUGINS

		// #region INSTALL ALL PLUGINS
		$("#install_all_plugin_button").on("click", function (e) {
			e.preventDefault();
			Swal.fire({
				titleText: "Confirm, please.",
				text: "Do you want to install and activate all the plugins of this list?",
				showDenyButton: true,
				showCancelButton: false,
				confirmButtonText: "Yes",
				denyButtonText: "No",
				icon: "question",
				customClass: {
					actions: "my-actions",
					confirmButton: "sweet-yes",
					denyButton: "sweet-no",
				},
			}).then((result) => {
				if (result.isConfirmed) {
					installAllPlugins();
				}
			});
		});

		function installAllPlugins() {
			toggleOverlay(true);
			let nonce = $("#_wpnonce_inst_all").val();
			let plugin_data = [];
			$.each($(".plugin_to_install"), function () {
				plugin_data.push($(this).val());
			});
			$.ajax({
				type: "post",
				url: ajaxurl,
				data: {
					nonce: nonce,
					plugin_data: plugin_data,
					action: "inslst_install_all_plugins",
				},
				success: function (data) {
					toggleOverlay(false);
					if (data.result === "success") {
						Swal.fire({
							title: "Congratulations!",
							text: "All your plugins have been installed and activated.",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "success",
						}).then((result) => {
							return false;
						});
					}
				},
				error: function (xhr, status, error) {
					toggleOverlay(false);
					var err = eval("(" + xhr.responseText + ")");
					console.log(err.Message);
					// console.log("error");
					// console.log(error);
				},
			});
		}
		// #endregion INSTALL ALL PLUGINS

		// #region INSTALL ALL PLUGIN OF LIST
		$(".install_list_button").on("click", function (e) {
			e.preventDefault();
			Swal.fire({
				titleText: "Confirm, please.",
				text: "Do you want to install and activate all the plugins of this list?",
				showDenyButton: true,
				showCancelButton: false,
				confirmButtonText: "Yes",
				denyButtonText: "No",
				icon: "question",
				customClass: {
					actions: "my-actions",
					confirmButton: "sweet-yes",
					denyButton: "sweet-no",
				},
			}).then((result) => {
				if (result.isConfirmed) {
					let nonce = $(this).data("nonce");
					let post_id = $(this).data("post-id");
					installAllPluginsOfList(nonce, post_id);
				}
			});
		});

		/**
		 * Install all plugins when user clicks on the link in the plugin lists page
		 * @param {string} nonce 
		 * @param {int} post_id 
		 */
		function installAllPluginsOfList(nonce, post_id) {
			toggleOverlay(true);
			$.ajax({
				type: "post",
				url: ajaxurl,
				data: {
					nonce: nonce,
					post_id: post_id,
					action: "inslst_install_list",
				},
				success: function (data) {
					toggleOverlay(false);
					if (data.result === "success") {
						Swal.fire({
							titleText: "Congratulations!",
							text: "All plugins of the list have been installed and activated.",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "success",
						}).then((result) => {
							return false;
						});
					} else if (data.result === "activation_error") {
						Swal.fire({
							titleText: "Warning!",
							text: "All plugins of the list have been installed but some of them couldn't be activated. The problem is probably due to some unmet requirement (i.e. these plugins could depend on another one, like Woocommerce, to work properly),",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "warning",
						}).then((result) => {
							return false;
						});
					} else {
						console.log("data");
						console.log(data);
						Swal.fire({
							titleText: "Error!",
							text: "Some error occurred trying to install this plugins list.",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "error",
						}).then((result) => {
							return false;
						});
					}
				},
				error: function (error) {
					toggleOverlay(false);
					console.log("error");
					console.log(error);
				},
			});
		}
		// #endregion INSTALL ALL PLUGIN OF LIST

		$("#cancel_edit_list").on("click", function () {
			$("#plugin_name").val("");
			$("#plugin_slug").val("");
			$("#plugins_result_list").find(".plugin_found_data").remove();
		});

		var getFileName = function (str) {
			return str.split("\\").pop().split("/").pop();
		};

		$("#export_list_button").on("click", function (e) {
			e.preventDefault();
			Swal.fire({
				titleText: "Confirm, please.",
				text: "Do you want to export this list and start downloading it?",
				showDenyButton: true,
				showCancelButton: true,
				confirmButtonText: "Yes",
				denyButtonText: "No",
				icon: "question",
			}).then((result) => {
				if (result.isConfirmed) {
					var postId = $(this).data("post-id");
					var nonce = $(this).data("nonce");

					// Crea un form temporaneo per il download del file
					var $form = $("<form>", {
						action: ajaxurl,
						method: "post",
					})
						.append(
							$("<input>", {
								type: "hidden",
								name: "action",
								value: "inslst_export_plugin_list",
							})
						)
						.append(
							$("<input>", {
								type: "hidden",
								name: "post_id",
								value: postId,
							})
						)
						.append(
							$("<input>", {
								type: "hidden",
								name: "security",
								value: nonce,
							})
						);

					$("body").append($form);
					$form.submit();
					$form.remove();
					// exportPluginList();
				}
			});
		});

		$("#import_plugin_list_button").on("click", function (e) {
			e.preventDefault();
			if ("" !== $("#import_list").val()) {
				if ($(this).data("is-premium") != "1") {
					Swal.fire({
						titleText: "Info",
						text: "You need to upgrade to get multi-lists feature.",
						showDenyButton: false,
						showCancelButton: false,
						confirmButtonText: "Ok",
						icon: "info",
					}).then(() => {
						return;
					});
				} else {
					var fakename = $("#import_list").val();
					var fname = getFileName(fakename);
					Swal.fire({
						title: "Do you want to import the plugin list " + fname + "?",
						showDenyButton: true,
						showCancelButton: false,
						confirmButtonText: "Yes",
						denyButtonText: "No",
					}).then((result) => {
						if (result.isConfirmed) {
							var filename = $("#import_list").val().split("\\").pop();
							if ("" !== filename) {
								var ext = filename.split(".").pop();
								if ("csv" !== ext) {
									Swal.fire({
										title: "Sorry, only csv format is accepted.",
										showDenyButton: false,
										showCancelButton: false,
										confirmButtonText: "Ok",
									}).then((result) => {
										$("#import_list").val(null);
										return;
									});
								}
								importPluginList();
							}
						}
					});
				}
			} else {
				Swal.fire({
					title: "Please, select a file before to click the Import button.",
					showDenyButton: false,
					showCancelButton: false,
					confirmButtonText: "Ok",
					icon: "",
					iconColor: "",
					confirmButtonColor: "",
					denyButtonColor: "",
					cancelButtonColor: "",
				}).then((result) => {
					if (result.isConfirmed) {
						return;
					}
				});
			}
		});

		function importPluginList() {
			toggleOverlay(true);
			$("#import_plugin_list_form").ajaxSubmit({
				success: function (data) {
					if (data.result === "success") {
						toggleOverlay(false);
						Swal.fire({
							titleText: "Success!",
							text: "File successfully imported and saved to uploads directory!",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "success",
							customClass: {
								actions: "my-actions",
								confirmButton: "sweet-yes",
								denyButton: "sweet-no",
							},
						}).then((result) => {
							return;
						});
						$("#import_list").val(null).trigger("change");
					} else if (data.result === "upgrade_needed") {
						toggleOverlay(false);
						Swal.fire({
							titleText: "Info",
							text: "You need to upgrade to get multi-lists feature.",
							showDenyButton: false,
							showCancelButton: false,
							confirmButtonText: "Ok",
							icon: "info",
						}).then(() => {
							return;
						});
						$("#import_list").val(null).trigger("change");
					}
				},
				error: function (xhr, ajaxOptions, thrownError) {
					toggleOverlay(false);
					var err = eval("(" + xhr.responseText + ")");
					alert(
						"Something went wrong. Please retry.\n Error message = " +
						err.Message
					);
					console.log(err.Message);
				},
			});

			return false;
		}

		function checkFile(e) {
			return "" === $("#import_list").val() ? false : true;
		}

		$("input[type=file]").change(function (e) {
			if (checkFile()) {
				var filename = e.currentTarget.files[0].name;
				$("span#import_list_filename").text(filename);
				$("#import_plugin_list_button").removeAttr("disabled");
			} else {
				$("#import_plugin_list_button").prop("disabled", true);
			}
		});

		function isEmpty(value) {
			return (
				value == null ||
				(typeof value === "string" && value.trim().length === 0)
			);
		}

		$("#load_local_plugins:not(.disabled)").on("click", function (e) {
			searchPluginsLocally();
		});
		/**
		 * search for plugins in local files
		 * @param {string} str
		 */
		function searchPluginsLocally() {
			togglePluginsOverlay(true);
			var data = {
				action: "inslst_get_installed_plugins",
			};
			$.post(ajaxurl, data, function (result) {
				if (result) {
					var response = JSON.parse(result);
					$("#plugins_result_list").find(".plugin_found_data").remove();
					var imgurl;
					$.each(response, function (index, entry) {
						if( entry.icons != null ){
							if (!isEmpty(entry.icons["svg"])) {
								imgurl = entry.icons["svg"];
							} else if (!isEmpty(entry.icons["1x"])) {
								imgurl = entry.icons["1x"];
							} else if (!isEmpty(entry.icons["2x"])) {
								imgurl = entry.icons["2x"];
							} else {
								imgurl = entry.icons["default"];
							}
						} else {
							imgurl = entry.imgurl;
						}
						$("#plugins_result_list").append(
							'<div class="plugin_found_data" data-plugin-name="' +
							entry.plugin_name +
							'" data-plugin-slug="' +
							entry.plugin_slug +
							'" data-plugin-icon="' +
							imgurl +
							'"><img src="' +
							imgurl +
							'" /><span>' +
							entry.plugin_name +
							"</span><span>" +
							entry.plugin_slug +
							"</span></div>"
						);
					});
					togglePluginsOverlay(false);
				} else {
					togglePluginsOverlay(false);
				}
			});
		}

		/**
		 * seacrh plugin using instalist API from codingfix.com
		 * @param {string} str
		 */
		function searchPlugins(str, page) {
			console.log('str is ' + str);
			togglePluginsOverlay(true);
			var data = {
				action: "inslst_get_plugins_from_repo",
				needle: str,
				page: page,
			};
			$.post(ajaxurl, data, function (result) {
				if (result) {
					console.log('result');
					console.log(result);
					var response = JSON.parse(result);
					console.log(response);
					$("#plugins_result_list").find(".plugin_found_data").remove();
					var imgurl;
					// $.each(response.plugins_data, function (key, entry) {
					$.each(response, function (key, entry) {
						if( entry.icons != null ){
							if (!isEmpty(entry.icons["svg"])) {
								imgurl = entry.icons["svg"];
							} else if (!isEmpty(entry.icons["1x"])) {
								imgurl = entry.icons["1x"];
							} else if (!isEmpty(entry.icons["2x"])) {
								imgurl = entry.icons["2x"];
							} else {
								imgurl = entry.icons["default"];
							}
						} else {
							imgurl = entry.imgurl;
						}

						var bgclass = '';
						if (entry.installed === 1) {
							bgclass = 'installed'
						} else {
							bgclass = '';
						}

						$("#plugins_result_list").append(
							'<div class="plugin_found_data ' + bgclass + '" data-plugin-name="' +
							entry.plugin_name +
							'" data-plugin-slug="' +
							entry.plugin_slug +
							'" data-plugin-icon="' +
							imgurl +
							'"><img src="' +
							imgurl +
							'" /><span>' +
							entry.plugin_name +
							"</span><span>" +
							entry.plugin_slug +
							"</span></div>"
						);
					});
					togglePluginsOverlay(false);
				} else {
				}
			});
		}

		$("#plugin_name").on("keyup", function (e) {
			if (e.which <= 90 && e.which >= 65) {
				const $this = $(this);
				pluginNameKeyup($this);
			}
		});

		//commented because the click on found plugins triggers this event and then is like a click on Next button
		// $("#plugin_name").on("change", function () {
		// 	const $this = $(this);
		// 	pluginNameKeyup($this);
		// });

		function pluginNameKeyup($this) {
			if ("undefined" !== typeof $(".plugin_found_data")) {
				$("#plugins_result_list").find(".plugin_found_data").remove();
			}
			if ($this.val().length === 0) {
				// $("#plugins_result_list").empty();
				if ("undefined" !== typeof $(".plugin_found_data")) {
					$("#plugins_result_list").find(".plugin_found_data").remove();
				}
				$("#plugin_slug").val("");
			}
			if ($this.val().length < 4) {
				if ("undefined" !== typeof $(".plugin_found_data")) {
					$("#plugins_result_list").find(".plugin_found_data").remove();
				}
				return;
			}
			$('#current_page').val('1');
			searchPlugins($this.val(), 1);
		}

		$(document).on('click', '.paginating', function (e) {
			e.preventDefault();
			var el = document.querySelector('#plugins_result_list');

			el.scrollTop = el.scrollHeight;
			el.scrollTop = 0;
			var currentPage = parseInt($('#current_page').val());
			if ($(this).hasClass('paginating-prev')) {
				if (1 === currentPage) {
					return;
				}
				searchPlugins($("#plugin_name").val(), currentPage - 1);
				$('#current_page').val(currentPage - 1);
			}
			if ($(this).hasClass('paginating-next')) {
				// if (1 === current_page) {
				// 	return;
				// }
				console.log('PLUGIN_NAME IS ' + $("#plugin_name").val());
				searchPlugins($("#plugin_name").val(), currentPage + 1);
				$('#current_page').val(currentPage + 1);
			}

		});

		// the following function is the result of merging what I have dound  at https://stackoverflow.com/questions/7225407/convert-camelcasetext-to-title-case-text and https://stackoverflow.com/questions/68677953/javascript-method-to-capitalize-the-first-letter-of-every-word-and-also-every-wo
		function toSpaceCase(str) {
			return str
				.toLowerCase()
				.replace(/[-_]/g, " ")
				.replace(/(^\w)|([-\s]\w)/g, (match) => match.toUpperCase())
				.replace(/\s+/g, " ")
				.trim();
		}

		// $("#plugin_slug").on("keyup", function () {
		// 	if ($(this).val().length === 0) {
		// 		$(this).sibling("#plugin_name").val("");
		// 		return;
		// 	}
		// 	if ($(this).val().length < 4) {
		// 		return;
		// 	}
		// 	console.log(toSpaceCase($(this).val()));
		// 	$(this)
		// 		.parent(".field")
		// 		.siblings(".field")
		// 		.find("#plugin_name")
		// 		.val(toSpaceCase($(this).val()));
		// 	$(this)
		// 		.parent(".field")
		// 		.siblings(".field")
		// 		.find("#plugin_name")
		// 		.trigger("keyup");
		// });

		$(document).on("click", ".plugin_found_data", function (e) {
			if (!$(this).hasClass('installed')) {
				let pluginFoundName = $(this).data("plugin-name");
				let pluginFoundSlug = $(this).data("plugin-slug");
				let pluginFoundIcon = $(this).data("plugin-icon");
				$("#plugin_name").val(pluginFoundName);
				$("#plugin_slug").val(pluginFoundSlug);
				$("#plugin_icon").val(pluginFoundIcon);
			}
		});

		$("input[type=file]").on("click", function (e) {
			$(this).val(null);
		});

		function noTitleAlert() {
			Swal.fire({
				titleText: "Warning!",
				text: "You must provide a List name before you can save your list.",
				showDenyButton: false,
				showCancelButton: false,
				confirmButtonText: "Ok",
				icon: "warning",
			}).then((result) => {
				return;
			});
		}

		$("form#post").on("submit", function (e) {
			var title = $("#title").val();
			if (!title) {
				noTitleAlert();
				e.preventDefault();
				return false;
			}
		});

		if ($("#post_type").val() === "inslst_plugin_list") {
			$("#title-prompt-text").text("Add Plugin List name");
		}

		checkFile();

		// Verifica se l'utente ha la versione premium

		var isPremium = true; // Implementa la tua logica per verificare la versione premium
		if ($("#import_plugin_list_button").data("is-premium") === "0") {
			isPremium = false;
		}

		if (!isPremium) {
			// Verifica il numero di liste esistenti
			$.ajax({
				url: ajaxurl,
				type: "post",
				data: {
					action: "inslst_check_plugin_list_count",
					security: instalistAdmin.nonce,
				},
				success: function (response) {
					if (response.found_posts >= 1) {
						// Disabilita i pulsanti e i link "Add Plugin List"
						$("a.page-title-action")
							.attr("href", "#")
							.click(function (e) {
								e.preventDefault();
								Swal.fire({
									titleText: "Confirm, please.",
									text: "You cannot create more than one plugin list with the free version. Do you want to upgrade to the Premium version?",
									showDenyButton: true,
									showCancelButton: false,
									confirmButtonText: "Yes",
									icon: "question",
									iconColor: "",
									customClass: {
										confirmButton: "sweet-yes",
										denyButton: "sweet-no",
									},
								}).then((result) => {
									if (result.isConfirmed) {
										return;
									}
								});
							});
						$('a[href*="post-new.php?post_type=inslst_plugin_list"]')
							.attr("href", "#")
							.click(function (e) {
								e.preventDefault();
								Swal.fire({
									titleText: "Confirm, please.",
									text: "You cannot create more than one plugin list with the free version. Do you want to upgrade to the Premium version?",
									showDenyButton: true,
									showCancelButton: false,
									confirmButtonText: "Yes",
									icon: "question",
									iconColor: "",
									customClass: {
										confirmButton: "sweet-yes",
										denyButton: "sweet-no",
									},
								}).then((result) => {
									if (result.isConfirmed) {
										return;
									}
								});
							});
					}
				},
			});
		}
	}); //ready
})(jQuery);
