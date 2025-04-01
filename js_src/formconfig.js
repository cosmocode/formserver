const formconfig = {
  "form": {
    "fieldset0": {
      "type": "fieldset",
      "column": "is-full",
      "children": {
        "radioset1": {
          "type": "radioset",
          "label": "Toggle different fieldsets (hidden fieldsets lose all their values when the form is submitted/saved!)",
          "column": "is-half",
          "alignment": "vertical",
          "validation": {
            "required": false
          },
          "choices": [
            "Static elements",
            "Dynamic elements",
            "Fieldset columns",
            "Tables"
          ],
          "default": "Dynamic elements"
        },
        "hr0": {
          "type": "hr",
          "column": "is-full",
          "color": "#f5f5f5",
          "height": 2
        },
        "fieldset_static": {
          "type": "fieldset",
          "label": "Static form elements",
          "column": "is-half",
          "visible": "fieldset0.radioset1 == 'Static elements'",
          "toggle": {
            "field": "fieldset0.radioset1",
            "value": "Static elements"
          },
          "children": {
            "markdown0": {
              "type": "markdown",
              "label": "markdown",
              "markdown": "Hello _Markdown_!"
            },
            "image0": {
              "type": "image",
              "label": "Image",
              "src": "logo.png"
            },
            "download0": {
              "type": "download",
              "label": "Download logo.png from data dir",
              "href": "logo.png"
            },
            "hidden": {
              "type": "hidden",
              "value": "I am not visable"
            }
          }
        },
        "fieldset_dynamic": {
          "type": "fieldset",
          "label": "Dynamic elements",
          "visible": "fieldset0.radioset1 == 'Dynamic elements'",
          "children": {
            "dynamic_left": {
              "type" : "fieldset",
              "column": "is-half",
              "children": {
                "textinput1": {
                  "type": "textinput",
                  "label": "Textinput",
                  "validation": {
                    "match": "/[a-zA-Z0-9]/",
                    "required": false
                  },
                  "readonly": false,
                  "tooltip": "Short tooltip text. Or a little longer tooltip text. Use modals for really long texts or text with formatting.",
                  "modal": "Modal content"
                },
                "numberinput1": {
                  "type": "numberinput",
                  "label": "Numberinput",
                  "tooltip": "number tooltip text",
                  "validation": {
                    "min": 10,
                    "max": 20
                  }
                },
                "textarea1": {
                  "type": "textarea",
                  "label": "Textarea",
                  "tooltip": "textarea tooltip text",
                  "rows": 8
                },
                "date1": {
                  "type": "date",
                  "label": "Date",
                  "tooltip": "date tooltip text"
                },
                "time1": {
                  "type": "time",
                  "label": "Time",
                  "tooltip": "time tooltip text"
                },
                "datetime1": {
                  "type": "datetime",
                  "label": "Datetime",
                  "tooltip": "datetime tooltip text"
                },
                "email1": {
                  "type": "email",
                  "label": "Email (cc)",
                  "tooltip": "email tooltip text"
                }
              }
            },
            "dynamic_right": {
              "type" : "fieldset",
              "column": "is-half",
              "children": {
                "radioset1": {
                  "type": "radioset",
                  "alignment": "vertical",
                  "label": "Radioset",
                  "choices": [
                    "Choice #1",
                    "Choice #2",
                    "Choice #3"
                  ],
                  "modal": "###This is a modal. {.title .is-1}\n\nWith paragraphs.\n\n![](logo.png)\n\nAnd images.\n\nAny markdown goes.\n",
                  "tooltip": "radioset tooltip text"
                },
                "dropdown1": {
                  "type": "dropdown",
                  "label": "Dropdown",
                  "empty_label": "Please choose",
                  "tooltip": "dropdown tooltip text",
                  "validation": {
                    "required": true
                  },
                  "choices": [
                    "Choice A",
                    "Choice B",
                    "Choice C"
                  ]
                },
                "dropdown2": {
                  "type": "dropdown",
                  "multiselect": true,
                  "label": "Multiselect (dropdown)",
                  "tooltip": "multiselect tooltip text",
                  "validation": {
                    "required": true
                  },
                  "conditional_choices": [
                    {
                      "choices": ["Choice visible unconditionally"]
                    },
                    {
                      "choices":
                        [
                          "Sub-choice of #1"
                        ],
                      "visible": "fieldset0.fieldset_dynamic.dynamic_right.radioset1 == 'Choice #1'"
                    },
                    {
                      "choices":
                        [
                          "NEW Sub-choice of #1 or #2"
                        ],
                      "visible": "fieldset0.fieldset_dynamic.dynamic_right.radioset1 IN ['Choice #1', 'Choice #2']"
                    },
                    {
                      "choices": [
                        "Sub-choice of #2",
                        "Sub-choice of all Except #1"
                      ],
                      "visible": "fieldset0.fieldset_dynamic.dynamic_right.radioset1 == 'Choice #2'"
                    },
                    {
                      "choices": ["Sub-choice of all Except #1"],
                      "visible": "fieldset0.fieldset_dynamic.dynamic_right.radioset1 != 'Choice #1'"
                    }
                  ]
                },
                "checklist1": {
                  "type": "checklist",
                  "alignment": "vertical",
                  "label": "Checklist",
                  "tooltip": "checklist tooltip text",
                  "choices": [
                    "Choice #1",
                    "Choice #2 [a nice link](https://www.cosmocode.de)",
                    "Choice #3"
                  ]
                },
                "upload1": {
                  "type": "upload",
                  "label": "Upload",
                  "tooltip": "upload tooltip text",
                  "validation": {
                    "filesize": "2MB",
                    "fileext": "jpg, pdf, txt"
                  }
                },
                "signature": {
                  "type": "signature",
                  "label": "signature",
                  "height": 400,
                  "width": 600,
                  "tooltip": "signature tooltip text",
                  "validation": {
                    "required": true
                  }
                }
              }
            }
          }
        }
      }
    },
    "fieldset4": {
      "type": "fieldset",
      "column": "is-full",
      "label": "Fieldset forcing 100% width for its children",
      "visible": "fieldset0.radioset1 == 'Fieldset columns'",
      "toggle": {
        "field": "fieldset0.radioset1",
        "value": "Fieldset columns"
      },
      "children": {
        "fieldset5": {
          "type": "fieldset",
          "column": "is-half",
          "children": {
            "fieldset5a": {
              "type": "fieldset",
              "label": "Fieldset 2/6 width",
              "column": "is-two-thirds",
              "children": {
                "textarea3": {
                  "type": "textarea",
                  "validation": {
                    "required": false
                  }
                }
              }
            },
            "fieldset5b": {
              "type": "fieldset",
              "label": "Fieldset 1/6 width",
              "column": "is-one-third",
              "children": {
                "textarea4": {
                  "type": "textarea",
                  "validation": {
                    "required": false
                  }
                }
              }
            }
          }
        },
        "fieldset6": {
          "type": "fieldset",
          "column": "is-half",
          "children": {
            "fieldset6a": {
              "type": "fieldset",
              "label": "Fieldset 1/4 width",
              "column": "is-half",
              "children": {
                "textarea4": {
                  "type": "textarea",
                  "validation": {
                    "required": false
                  }
                }
              }
            },
            "fieldset6b": {
              "type": "fieldset",
              "label": "Fieldset 1/4 width",
              "column": "is-half",
              "children": {
                "textarea4": {
                  "type": "textarea",
                  "validation": {
                    "required": false
                  }
                }
              }
            }
          }
        }
      }
    },
    "field_tabelle_tablestyle": {
      "type": "fieldset",
      "label": "Tabelle, tablestyle true, scrollable true",
      "tablestyle": true,
      "visible": "fieldset0.radioset1 == 'Tables'",
      "children": {
        "hk": {
          "type": "table",
          "label": "HK",
          "column": "is-one-fifth",
          "scrollable": true,
          "repeat": 12,
          "children": {
            "nutzungszeit": {
              "type": "textinput",
              "label": "Nutzungszeit (RO)"
            },
            "heizkennlinie": {
              "type": "textinput",
              "label": "Heizkennlinie",
              "validation": {
                "required": false
              }
            },
            "vorlauftemperatur": {
              "type": "textinput",
              "label": "Vorlauftemperatur",
              "placeholder": "Vorlauftemperatur",
              "validation": {
                "required": false
              }
            },
            "temp_min": {
              "type": "textinput",
              "label": "min. (°C)",
              "validation": {
                "required": false
              }
            },
            "temp_max": {
              "type": "textinput",
              "label": "max. (°C)",
              "validation": {
                "required": false
              }
            },
            "temp_stw": {
              "type": "textinput",
              "label": "STW (°C)",
              "validation": {
                "required": false
              }
            },
            "hydr_einstellungen_pumpe": {
              "type": "textinput",
              "label": "Hydr. Einstellungen Umwälzpumpe",
              "validation": {
                "required": false
              }
            },
            "konstant": {
              "type": "textinput",
              "label": "konstant",
              "validation": {
                "required": false
              }
            },
            "variabel": {
              "type": "textinput",
              "label": "variabe",
              "validation": {
                "required": false
              }
            },
            "0-10v": {
              "type": "textinput",
              "label": "0-10V",
              "validation": {
                "required": false
              }
            },
            "hydr_einstellungen_srv": {
              "type": "textinput",
              "label": "Hydr. Einstellungen SRV",
              "validation": {
                "required": false
              }
            },
            "skalenwert": {
              "type": "textinput",
              "label": "Skalenwert",
              "validation": {
                "required": false
              }
            }
          }
        }
      }
    }
  },
  "lang": {
    "error_notfound": "The resource you are looking for could not be found.",
    "error_required": "This is a required field",
    "error_min": "Value must be at least",
    "error_max": "Value must not exceed",
    "error_match": "Value must match",
    "error_fileext": "Allowed file types are",
    "error_filesize": "Maximum file size is",
    "form_valid": "Form was saved successfully",
    "form_invalid": "Form was saved successfully, but it still contains errors.",
    "send_success": "The form was sent successfully.",
    "send_prevented": "The form could not be sent because it contains errors.",
    "send_failed": "There was en error sending email. Please contact the administrator.",
    "label_signature_delete": "Clear input",
    "label_signature_replace": "Replace input",
    "email_text_attachments": "Look into the attachments",
    "button_save": "Save",
    "button_send": "Send",
    "button_clone": "Copy field",
    "button_upload": "Upload files",
    "button_upload_replace": "Replace uploaded files",
    "uploaded_file": "Saved file",
    "uploaded_original": "Original filename",
    "upload_info": "The selected files will be uploaded and checked after saving or sending the form:",
    "upload_error": "Upload is too large!"
  },
  "values": {
    "fieldset0": {
      "radioset1": "Dynamic elements"
    },
    "fieldset4": {
      "fieldset5": {
        "fieldset5a": {
          "textarea3": "column is-two-thirds within column is-half"
        }
      }
    }
  }
};
