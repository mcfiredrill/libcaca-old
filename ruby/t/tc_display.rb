require 'caca'

class TC_Canvas < Test::Unit::TestCase
    def test_create
        d = Caca::Display.new()
        assert_not_nil(d, 'Display creation failed')
    end
    def test_create_with_driver
        d = Caca::Display.new(Caca::Display.driver_list[0])
        assert_not_nil(d, 'Display creation failed')
    end
    def test_create_wrong_args
        c = Caca::Canvas.new(3, 3)
        assert_raise(RuntimeError){Caca::Display.new("plop")}
        assert_raise(RuntimeError){Caca::Display.new(c, "plop")}
        assert_raise(ArgumentError){Caca::Display.new("plop", "plop")}
        assert_raise(ArgumentError){Caca::Display.new(c, c)}
    end
    def test_create_from_canvas
        c = Caca::Canvas.new(3, 3)
        d = Caca::Display.new(c)
        assert_not_nil(d, 'Display creation failed')
        assert_equal(d.canvas, c, 'Wrong canvas')
    end
    def test_set_title
        c = Caca::Canvas.new(3, 3)
        d = Caca::Display.new(c)
        d.title = "Test !"
    end
    def test_set_cursor
        d = Caca::Display.new()
        d.cursor = 1
    end
    def test_get_mouse_coordinates
        c = Caca::Canvas.new(3, 3)
        d = Caca::Display.new(c)
        assert_not_nil(d.mouse_x)
        assert_not_nil(d.mouse_y)
    end
    def test_get_display_dimensions
        c = Caca::Canvas.new(3, 3)
        d = Caca::Display.new(c)
        assert_not_nil(d.height)
        assert_not_nil(d.width)
    end
    def test_get_time
        c = Caca::Canvas.new(3, 3)
        d = Caca::Display.new(c)
        assert_not_nil(d.time)
    end
end
