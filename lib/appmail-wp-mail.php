<?php

if ( function_exists( 'wp_mail' ) ) {
	/**
	 * wp_mail has been declared by another process or plugin, so you won't be able to use Jannes & Mannes AppMail until the problem is solved.
	 */
	add_action( 'admin_notices', 'wp_mail_already_declared_notice' );

	/**
	 * Display the notice that wp_mail function was declared by another plugin
	 *
	 * return void
	 */
	function wp_mail_already_declared_notice() {
		echo '<div class="error"><p>' . __( 'Jannes &amp; Mannes AppMail: wp_mail has been declared by another process or plugin, so you won\'t be able to use AppMail until the conflict is solved.' ) . '</p></div>';
	}

	return;
}

if ( ! function_exists( 'wp_mail' ) ) {
	function wp_mail( $to, $subject, $message, $headers = '', $attachments = array() ) {
		// Compact the input, apply the filters, and extract them back out

		/**
		 * Filter the wp_mail() arguments.
		 *
		 * @since 2.2.0
		 *
		 * @param array $args A compacted array of wp_mail() arguments, including the "to" email,
		 *                    subject, message, headers, and attachments values.
		 */
		$atts = apply_filters( 'wp_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) );

		if ( isset( $atts['to'] ) ) {
			$to = $atts['to'];
		}

		if ( isset( $atts['subject'] ) ) {
			$subject = $atts['subject'];
		}

		if ( isset( $atts['message'] ) ) {
			$message = $atts['message'];
		}

		if ( isset( $atts['headers'] ) ) {
			$headers = $atts['headers'];
		}

		if ( isset( $atts['attachments'] ) ) {
			$attachments = $atts['attachments'];
		}

		if ( ! is_array( $attachments ) ) {
			$attachments = explode( "\n", str_replace( "\r\n", "\n", $attachments ) );
		}

		// Create a new AppMail client using the server key you generate in our web interface
		$client = new \AppMail\Client( \JmAppMail\Admin::get_options()['api_key'] );

		// Create a new message
		$appmail_message = new \AppMail\SendMessage( $client );

		// Headers
		if ( empty( $headers ) ) {
			$headers = array();
		} else {
			if ( ! is_array( $headers ) ) {
				// Explode the headers out, so this function can take both
				// string headers and an array of headers.
				$tempheaders = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
			} else {
				$tempheaders = $headers;
			}
			$headers = array();
			$cc      = array();
			$bcc     = array();

			// If it's actually got contents
			if ( ! empty( $tempheaders ) ) {
				// Iterate through the raw headers
				foreach ( (array) $tempheaders as $header ) {
					if ( strpos( $header, ':' ) === false ) {
						if ( false !== stripos( $header, 'boundary=' ) ) {
							$parts    = preg_split( '/boundary=/i', trim( $header ) );
							$boundary = trim( str_replace( array( "'", '"' ), '', $parts[1] ) );
						}
						continue;
					}
					// Explode them out
					list( $name, $content ) = explode( ':', trim( $header ), 2 );

					// Cleanup crew
					$name    = trim( $name );
					$content = trim( $content );

					switch ( strtolower( $name ) ) {
						// Mainly for legacy -- process a From: header if it's there
						case 'from':
							$bracket_pos = strpos( $content, '<' );
							if ( $bracket_pos !== false ) {
								// Text before the bracketed email is the "From" name.
								if ( $bracket_pos > 0 ) {
									$from_name = substr( $content, 0, $bracket_pos - 1 );
									$from_name = str_replace( '"', '', $from_name );
									$from_name = trim( $from_name );
								}

								$from_email = substr( $content, $bracket_pos + 1 );
								$from_email = str_replace( '>', '', $from_email );
								$from_email = trim( $from_email );

								// Avoid setting an empty $from_email.
							} elseif ( '' !== trim( $content ) ) {
								$from_email = trim( $content );
							}
							break;
						case 'content-type':
							if ( strpos( $content, ';' ) !== false ) {
								list( $type, $charset_content ) = explode( ';', $content );
								$content_type = trim( $type );
								if ( false !== stripos( $charset_content, 'charset=' ) ) {
									$charset = trim( str_replace( array( 'charset=', '"' ), '', $charset_content ) );
								} elseif ( false !== stripos( $charset_content, 'boundary=' ) ) {
									$boundary = trim( str_replace( array( 'BOUNDARY=', 'boundary=', '"' ), '',
										$charset_content ) );
									$charset  = '';
								}

								// Avoid setting an empty $content_type.
							} elseif ( '' !== trim( $content ) ) {
								$content_type = trim( $content );
							}
							break;
						case 'cc':
							$cc = array_merge( (array) $cc, explode( ',', $content ) );
							break;
						case 'bcc':
							$bcc = array_merge( (array) $bcc, explode( ',', $content ) );
							break;
						default:
							// Add it to our grand headers array
							$headers[ trim( $name ) ] = trim( $content );
							break;
					}
				}
			}
		}

		/**
		 * Filter the email address to send from.
		 *
		 * @since 2.2.0
		 *
		 * @param string $from_email Email address to send from.
		 */
		//$appmail_message->from( apply_filters( 'wp_mail_from', $from_email ) );
		$appmail_message->from( \JmAppMail\Admin::get_options()['from_email'] );

		/**
		 * Filter the name to associate with the "from" email address.
		 *
		 * @since 2.3.0
		 *
		 * @param string $from_name Name associated with the "from" email address.
		 */
		// TODO set from name

		// Set destination addresses
		if ( ! is_array( $to ) ) {
			$to = explode( ',', $to );
		}

		foreach ( (array) $to as $recipient ) {
			// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
			$recipient_name = '';
			if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
				if ( count( $matches ) == 3 ) {
					$recipient_name = $matches[1];
					$recipient      = $matches[2];
				}
			}
			$appmail_message->to( $recipient ); // TODO add name
		}

		// Set mail's subject and body
		$appmail_message->subject( $subject );

		// Add any CC and BCC recipients
		if ( ! empty( $cc ) ) {
			foreach ( (array) $cc as $recipient ) {
				// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
				$recipient_name = '';
				if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
					if ( count( $matches ) == 3 ) {
						$recipient_name = $matches[1];
						$recipient      = $matches[2];
					}
				}
				$appmail_message->cc( $recipient ); // TODO add name
			}
		}

		if ( ! empty( $bcc ) ) {
			foreach ( (array) $bcc as $recipient ) {
				// Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
				$recipient_name = '';
				if ( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
					if ( count( $matches ) == 3 ) {
						$recipient_name = $matches[1];
						$recipient      = $matches[2];
					}
				}

				$appmail_message->bcc( $recipient ); // TODO add name
			}
		}

		// Set Content-Type and charset
		// If we don't have a content-type from the input headers
		if ( ! isset( $content_type ) ) {
			$content_type = 'text/plain';
		}

		/**
		 * Filter the wp_mail() content type.
		 *
		 * @since 2.3.0
		 *
		 * @param string $content_type Default wp_mail() content type.
		 */
		$content_type = apply_filters( 'wp_mail_content_type', $content_type );

		// Set whether it's plaintext, depending on $content_type
		if ( 'text/html' == $content_type ) {
			$appmail_message->htmlBody( $message );
		} else {
			$appmail_message->plainBody( $message );
		}

		// If we don't have a charset from the input headers
		if ( ! isset( $charset ) ) {
			$charset = get_bloginfo( 'charset' );
		}

		// Set the content-type and charset

		/**
		 * Filter the default wp_mail() charset.
		 *
		 * @since 2.3.0
		 *
		 * @param string $charset Default email charset.
		 */
		//$phpmailer->CharSet = apply_filters( 'wp_mail_charset', $charset );
		// TODO add charset to appmail mailer

		$appmail_message->header( 'x-jannes-mannes-appmail', $_SERVER['SERVER_NAME'] );

		// Set custom headers
		if ( ! empty( $headers ) ) {
			foreach ( (array) $headers as $name => $content ) {
				$appmail_message->header( $name, $content );
			}

			if ( false !== stripos( $content_type, 'multipart' ) && ! empty( $boundary ) ) {
				$appmail_message->header( 'Content-Type', sprintf( "%s;\n\t boundary=\" %s\"", $content_type,
					$boundary ) ); // TODO not sure if this is correct
			}
		}

		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $attachment ) {
				$appmail_message->attach( basename( $attachment ), '', file_get_contents( $attachment ) );
			}
		}

		// Send!
		try {
			$result = $appmail_message->send();

			return $result->size();
		} catch ( \AppMail\Error $e ) {
			error_log( 'Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() . '. Stack trace: ' . $e->getTraceAsString() );

			$mail_error_data = compact( 'to', 'subject', 'message', 'headers', 'attachments' );

			/**
			 * Fires after a phpmailerException is caught.
			 *
			 * @since 4.4.0
			 *
			 * @param WP_Error $error A WP_Error object with the phpmailerException code, message, and an array
			 *                        containing the mail recipient, subject, message, headers, and attachments.
			 */
			do_action( 'wp_mail_failed', new WP_Error( $e->getCode(), $e->getMessage(), $mail_error_data ) );

			return false;
		}
	}
}