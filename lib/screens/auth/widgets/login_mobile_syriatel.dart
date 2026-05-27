import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'package:cirilla/mixins/mixins.dart';
import 'package:cirilla/screens/auth/widgets/verify_code.dart';
import 'package:cirilla/utils/utils.dart';
import 'package:cirilla/screens/home/home.dart';
import 'package:cirilla/store/auth/auth_store.dart';
import 'package:cirilla/store/auth/syriatel_otp_store.dart';
import 'package:cirilla/themes/default/auth/login_mobile_form.dart';
import 'package:cirilla/widgets/cirilla_phone_input/phone_number.dart';

/// Login via Syriatel OTP
class LoginMobileSyriatel extends StatefulWidget {
  final String type;
  final int lengthVerify;

  const LoginMobileSyriatel({
    Key? key,
    required this.type,
    this.lengthVerify = 6,
  }) : super(key: key);

  @override
  State<LoginMobileSyriatel> createState() => _LoginMobileSyriatelState();
}

class _LoginMobileSyriatelState extends State<LoginMobileSyriatel>
    with SnackMixin, LoadingMixin, AppBarMixin {
  late AuthStore _authStore;
  late SyriatelOtpStore _otpStore;

  bool _loading = false;

  @override
  void didChangeDependencies() {
    _authStore = Provider.of<AuthStore>(context);
    _otpStore = _authStore.syriatelOtpStore;
    super.didChangeDependencies();
  }

  setLoading(bool value) {
    setState(() {
      _loading = value;
    });
  }

  onSubmit({required PhoneNumber phoneNumber}) async {
    if (phoneNumber.number == null ||
        phoneNumber.number!.length < 9 ||
        phoneNumber.number!.length > 13) {
      showError(context, 'رقم الهاتف غير صالح');
      return;
    }

    setLoading(true);

    String? mobileNo = phoneNumber.number;
    // Ensure Syrian format 09XXXXXXXX
    if (mobileNo != null && !mobileNo.startsWith('0')) {
      mobileNo = '0$mobileNo';
    }

    try {
      await _otpStore.sendOtp(mobileNo!);
      setLoading(false);

      if (mounted) {
        await showModalBottomSheet(
          context: context,
          isScrollControlled: true,
          shape: const RoundedRectangleBorder(
            borderRadius: BorderRadius.only(
              topLeft: Radius.circular(20),
              topRight: Radius.circular(20),
            ),
          ),
          builder: (BuildContext context) {
            String checkOTP = '';
            return StatefulBuilder(builder: (BuildContext context, setState) {
              return VerifyCode(
                lengthVerify: widget.lengthVerify,
                onReSend: () async {
                  try {
                    await _otpStore.sendOtp(mobileNo!);
                  } catch (e) {
                    if (context.mounted) showError(context, e);
                  }
                },
                showError: checkOTP == '0',
                onVerify: (smsCode) async {
                  try {
                    final success = await _otpStore.verifyOtp(mobileNo!, smsCode);
                    setState(() => checkOTP = success ? '1' : '0');
                    if (success && context.mounted) {
                      Navigator.popUntil(
                          context, ModalRoute.withName(HomeScreen.routeName));
                    }
                  } catch (e) {
                    avoidPrint('verifyOtp error in UI: $e');
                    setState(() => checkOTP = '0');
                    if (context.mounted) showError(context, e);
                  }
                },
              );
            });
          },
        );
      }
    } catch (e) {
      setLoading(false);
      if (mounted) showError(context, e);
    }
  }

  @override
  Widget build(BuildContext context) {
    return LoginMobileForm(
      loading: _loading,
      onSubmit: onSubmit,
    );
  }
}
